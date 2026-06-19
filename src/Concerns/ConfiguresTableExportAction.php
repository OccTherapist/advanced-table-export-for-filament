<?php

namespace OccTherapist\AdvancedTableExportForFilament\Concerns;

use Closure;
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
use Filament\Tables\Columns\Column;
use Illuminate\Support\HtmlString;
use OccTherapist\AdvancedTableExportForFilament\AdvancedTableExportForFilamentPlugin;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Services\TableExportCoordinator;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportColumnCollection;

trait ConfiguresTableExportAction
{
    protected bool $usesSelectedRecords = false;

    /** @var array<int, Column> */
    protected array $additionalExportColumns = [];

    protected ?Closure $modifyExportQueryUsing = null;

    /**
     * @param  array<int, Column>  $columns
     */
    public function withColumns(array $columns): static
    {
        $this->additionalExportColumns = $columns;

        return $this;
    }

    public function modifyExportQueryUsing(?Closure $callback): static
    {
        $this->modifyExportQueryUsing = $callback;

        return $this;
    }

    protected function configureTableExportAction(bool $usesSelectedRecords): void
    {
        $this->usesSelectedRecords = $usesSelectedRecords;

        $this
            ->label(__('advanced-table-export-for-filament::export.action_label'))
            ->icon(config('advanced-table-export-for-filament.action_icon', 'heroicon-o-arrow-down-on-square'))
            ->modalHeading(__('advanced-table-export-for-filament::export.modal_heading'))
            ->modalSubmitActionLabel(__('advanced-table-export-for-filament::export.submit'))
            ->modalWidth('7xl')
            ->fillForm(fn (): array => [
                'format' => config('advanced-table-export-for-filament.default_format', ExportFormat::Xlsx->value),
                'page_orientation' => config('advanced-table-export-for-filament.default_page_orientation', 'landscape'),
                'preview_page' => 1,
                'enabled_columns' => array_keys($this->getExportableColumnOptions()),
            ])
            ->schema(fn (): array => $this->getExportFormSchema())
            ->action(function (array $data, TableExportCoordinator $coordinator) {
                return $coordinator->handle(
                    action: $this,
                    data: $data,
                    usesSelectedRecords: $this->usesSelectedRecords,
                    additionalColumns: $this->additionalExportColumns,
                    modifyExportQueryUsing: $this->modifyExportQueryUsing,
                    maxPdfRows: $this->getMaxPdfRows(),
                    maxExportRows: $this->getMaxExportRows(),
                );
            });
    }

    /**
     * @return array<int, mixed>
     */
    protected function getExportFormSchema(): array
    {
        $schema = $this->getExportOptionsSchema();

        if (! config('advanced-table-export-for-filament.disable_preview')) {
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
                    Html::make(function (Get $get, TableExportCoordinator $coordinator): HtmlString {
                        return new HtmlString(view('advanced-table-export-for-filament::export-preview', $coordinator->preview(
                            action: $this,
                            data: [
                                'format' => $get('format'),
                                'page_orientation' => $get('page_orientation'),
                                'preview_page' => $get('preview_page'),
                                'enabled_columns' => $get('enabled_columns'),
                                'file_name' => $get('file_name'),
                            ],
                            usesSelectedRecords: $this->usesSelectedRecords,
                            additionalColumns: $this->additionalExportColumns,
                            modifyExportQueryUsing: $this->modifyExportQueryUsing,
                            previewPerPage: $this->getPreviewPerPage(),
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
    protected function getExportOptionsSchema(): array
    {
        $schema = [
            Radio::make('format')
                ->label(__('advanced-table-export-for-filament::export.format'))
                ->options(ExportFormat::class)
                ->default(config('advanced-table-export-for-filament.default_format', ExportFormat::Xlsx->value))
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('preview_page', 1)),
            Radio::make('page_orientation')
                ->label(__('advanced-table-export-for-filament::export.orientation'))
                ->options([
                    'landscape' => __('advanced-table-export-for-filament::export.landscape'),
                    'portrait' => __('advanced-table-export-for-filament::export.portrait'),
                ])
                ->default(config('advanced-table-export-for-filament.default_page_orientation', 'landscape'))
                ->visible(fn (Get $get): bool => $get('format') === ExportFormat::Pdf->value),
        ];

        if (! config('advanced-table-export-for-filament.disable_file_name')) {
            $schema[] = TextInput::make('file_name')
                ->label(__('advanced-table-export-for-filament::export.file_name'))
                ->maxLength(255);
        }

        if (! config('advanced-table-export-for-filament.disable_filter_columns')) {
            $schema[] = CheckboxList::make('enabled_columns')
                ->label(__('advanced-table-export-for-filament::export.columns'))
                ->options(fn (): array => $this->getExportableColumnOptions())
                ->columns(2)
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('preview_page', 1));
        }

        return $schema;
    }

    /**
     * @return array<string, string>
     */
    protected function getExportableColumnOptions(): array
    {
        $table = $this->getTable();

        if ($table === null) {
            return [];
        }

        return ExportColumnCollection::labels(
            ExportColumnCollection::resolve(
                table: $table,
                additionalColumns: $this->additionalExportColumns,
                enabledColumnNames: null,
                includeHiddenColumns: true,
            ),
        );
    }

    protected function getMaxPdfRows(): int
    {
        $plugin = filament()->getCurrentPanel()?->getPlugin(AdvancedTableExportForFilamentPlugin::class);

        if ($plugin instanceof AdvancedTableExportForFilamentPlugin) {
            return $plugin->getMaxPdfRows();
        }

        return (int) config('advanced-table-export-for-filament.max_pdf_rows', 200);
    }

    protected function getMaxExportRows(): int
    {
        $plugin = filament()->getCurrentPanel()?->getPlugin(AdvancedTableExportForFilamentPlugin::class);

        if ($plugin instanceof AdvancedTableExportForFilamentPlugin) {
            return $plugin->getMaxExportRows();
        }

        return (int) config('advanced-table-export-for-filament.max_export_rows', 2000);
    }

    protected function getPreviewPerPage(): int
    {
        $plugin = filament()->getCurrentPanel()?->getPlugin(AdvancedTableExportForFilamentPlugin::class);

        if ($plugin instanceof AdvancedTableExportForFilamentPlugin) {
            return $plugin->getPreviewPerPage();
        }

        return (int) config('advanced-table-export-for-filament.preview_per_page', 25);
    }
}
