<?php

namespace OccTherapist\AdvancedTableExportForFilament\Services;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;

class TableExportCoordinator
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, mixed>  $additionalColumns
     */
    public function handle(Action | BulkAction $action, array $data, bool $usesSelectedRecords, array $additionalColumns = []): void
    {
        $format = ExportFormat::from($data['format'] ?? ExportFormat::Xlsx->value);

        Notification::make()
            ->title(__('advanced-table-export-for-filament::export.not_implemented_title'))
            ->body(__('advanced-table-export-for-filament::export.not_implemented_body', [
                'format' => $format->getLabel(),
            ]))
            ->warning()
            ->send();

        $action->halt();
    }
}
