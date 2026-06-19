<?php

namespace OccTherapist\AdvancedTableExportForFilament\Concerns;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use OccTherapist\AdvancedTableExportForFilament\AdvancedTableExportForFilamentPlugin;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Services\TableExportCoordinator;

trait ConfiguresTableExportAction
{
    protected bool $usesSelectedRecords = false;

    /** @var array<int, mixed> */
    protected array $additionalExportColumns = [];

    public function withColumns(array $columns): static
    {
        $this->additionalExportColumns = $columns;

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
            ->schema(fn (): array => $this->getExportFormSchema())
            ->action(fn (array $data, TableExportCoordinator $coordinator) => $coordinator->handle(
                action: $this,
                data: $data,
                usesSelectedRecords: $this->usesSelectedRecords,
                additionalColumns: $this->additionalExportColumns,
            ));
    }

  /**
   * @return array<int, mixed>
   */
    protected function getExportFormSchema(): array
    {
        if (config('advanced-table-export-for-filament.disable_preview')) {
            return $this->getExportOptionsSchema();
        }

        return [
            ...$this->getExportOptionsSchema(),
            Section::make(__('advanced-table-export-for-filament::export.preview'))
                ->schema([
                    View::make('advanced-table-export-for-filament::preview-placeholder'),
                ])
                ->columnSpanFull(),
        ];
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
                ->default(ExportFormat::Xlsx->value)
                ->required()
                ->live(),
            Radio::make('page_orientation')
                ->label(__('advanced-table-export-for-filament::export.orientation'))
                ->options([
                    'landscape' => __('advanced-table-export-for-filament::export.landscape'),
                    'portrait' => __('advanced-table-export-for-filament::export.portrait'),
                ])
                ->default(config('advanced-table-export-for-filament.default_page_orientation', 'landscape'))
                ->visible(fn (callable $get): bool => $get('format') === ExportFormat::Pdf->value),
        ];

        if (! config('advanced-table-export-for-filament.disable_file_name')) {
            $schema[] = TextInput::make('file_name')
                ->label(__('advanced-table-export-for-filament::export.file_name'))
                ->maxLength(255);
        }

        if (! config('advanced-table-export-for-filament.disable_filter_columns')) {
            $schema[] = CheckboxList::make('enabled_columns')
                ->label(__('advanced-table-export-for-filament::export.columns'))
                ->options([])
                ->columns(2);
        }

        return $schema;
    }

    protected function sendRowLimitExceededNotification(string $message): void
    {
        Notification::make()
            ->title($message)
            ->danger()
            ->send();
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
}
