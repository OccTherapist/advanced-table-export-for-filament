<?php

namespace OccTherapist\AdvancedTableExportForFilament\Concerns;

use Filament\Actions\Action as FormAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions as FormActions;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Services\TableExportCoordinator;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportColumnCollection;

trait ConfiguresTableExportAction
{
    use InteractsWithTableExportOptions;

    protected bool $usesSelectedRecords = false;

    protected function configureTableExportAction(bool $usesSelectedRecords): void
    {
        $this->usesSelectedRecords = $usesSelectedRecords;

        $options = fn (): TableExportOptions => $this->getTableExportOptions($usesSelectedRecords);

        $this
            ->label(__('advanced-table-export-for-filament::export.action_label'))
            ->icon(config('advanced-table-export-for-filament.action_icon', 'heroicon-o-arrow-down-on-square'))
            ->modalHeading(__('advanced-table-export-for-filament::export.modal_heading'))
            ->modalSubmitActionLabel(__('advanced-table-export-for-filament::export.submit'))
            ->modalWidth('7xl')
            ->fillForm(fn (): array => $this->getDefaultExportFormData($options()))
            ->schema(fn (): array => $this->getExportFormSchema($options()))
            ->action(function (array $data, TableExportCoordinator $coordinator) use ($options) {
                $exportOptions = $options();

                if ($this->shouldDirectDownload()) {
                    $data = array_merge($this->getDefaultExportFormData($exportOptions), $data);
                }

                return $coordinator->handle(
                    action: $this,
                    data: $data,
                    options: $exportOptions,
                );
            });

        if ($this->shouldDirectDownload()) {
            $this->modal(false);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultExportFormData(TableExportOptions $options): array
    {
        $table = $this->getTable();

        $enabledColumns = $table instanceof Table
            ? ($options->disableFilterColumns
                ? $options->resolveDefaultEnabledColumnNames($table)
                : $options->resolvePickerColumnNames($table))
            : [];

        return [
            'format' => $options->resolveFormat($options->defaultFormat->value)->value,
            'page_orientation' => $options->defaultPageOrientation,
            'preview_page' => 1,
            'enabled_columns' => $enabledColumns,
            'file_name' => $options->defaultFileName,
        ];
    }

    /**
     * @return array<int, mixed>
     */
    protected function getExportFormSchema(TableExportOptions $options): array
    {
        $schema = $this->getExportOptionsSchema($options);

        if (! $options->disablePreview) {
            $schema[] = Section::make(__('advanced-table-export-for-filament::export.preview'))
                ->schema([
                    Hidden::make('preview_page')->default(1)->live(),
                    FormActions::make([
                        FormAction::make('previewPrevious')
                            ->label(__('advanced-table-export-for-filament::export.preview_previous'))
                            ->color('gray')
                            ->action(function (Get $get, Set $set): void {
                                $set('preview_page', max(1, (int) $get('preview_page') - 1));
                            }),
                        FormAction::make('previewNext')
                            ->label(__('advanced-table-export-for-filament::export.preview_next'))
                            ->color('gray')
                            ->action(function (Get $get, Set $set): void {
                                $set('preview_page', (int) $get('preview_page') + 1);
                            }),
                    ]),
                    Html::make(function (Get $get, TableExportCoordinator $coordinator) use ($options): HtmlString {
                        return new HtmlString(view('advanced-table-export-for-filament::export-preview', $coordinator->preview(
                            action: $this,
                            data: [
                                'format' => $get('format'),
                                'page_orientation' => $get('page_orientation'),
                                'preview_page' => $get('preview_page'),
                                'enabled_columns' => $get('enabled_columns'),
                                'file_name' => $get('file_name'),
                            ],
                            options: $options,
                        ))->render());
                    })
                        ->columnSpanFull(),
                ])
                ->columnSpanFull();
        }

        return $schema;
    }

    /**
     * @return array<int, mixed>
     */
    protected function getExportOptionsSchema(TableExportOptions $options): array
    {
        $schema = [
            Radio::make('format')
                ->label($options->formatFieldLabel ?? __('advanced-table-export-for-filament::export.format'))
                ->options($options->availableFormatOptions())
                ->default($options->defaultFormat->value)
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('preview_page', 1)),
            Radio::make('page_orientation')
                ->label($options->pageOrientationFieldLabel ?? __('advanced-table-export-for-filament::export.orientation'))
                ->options([
                    'landscape' => __('advanced-table-export-for-filament::export.landscape'),
                    'portrait' => __('advanced-table-export-for-filament::export.portrait'),
                ])
                ->default($options->defaultPageOrientation)
                ->visible(fn (Get $get): bool => $get('format') === ExportFormat::Pdf->value),
        ];

        if (! $options->disableFileName) {
            $schema[] = TextInput::make('file_name')
                ->label($options->fileNameFieldLabel ?? __('advanced-table-export-for-filament::export.file_name'))
                ->default($options->defaultFileName)
                ->maxLength(255);
        }

        if (! $options->disableFilterColumns) {
            $schema[] = CheckboxList::make('enabled_columns')
                ->label($options->filterColumnsFieldLabel ?? __('advanced-table-export-for-filament::export.columns'))
                ->options(fn (): array => $this->getExportableColumnOptions($options))
                ->columns(2)
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('preview_page', 1));
        }

        return $schema;
    }

    /**
     * @return array<string, string>
     */
    protected function getExportableColumnOptions(TableExportOptions $options): array
    {
        $table = $this->getTable();

        if ($table === null) {
            return [];
        }

        return ExportColumnCollection::labels(
            ExportColumnCollection::resolve(
                table: $table,
                additionalColumns: $options->additionalColumns,
                enabledColumnNames: null,
                includeHiddenColumns: true,
                disableTableColumns: $options->disableTableColumns,
            ),
        );
    }
}
