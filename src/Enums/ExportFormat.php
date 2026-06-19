<?php

namespace OccTherapist\AdvancedTableExportForFilament\Enums;

use Filament\Support\Contracts\HasLabel;

enum ExportFormat: string implements HasLabel
{
    case Csv = 'csv';
    case Xlsx = 'xlsx';
    case Pdf = 'pdf';

    public function getLabel(): string
    {
        return match ($this) {
            self::Csv => __('advanced-table-export-for-filament::export.formats.csv'),
            self::Xlsx => __('advanced-table-export-for-filament::export.formats.xlsx'),
            self::Pdf => __('advanced-table-export-for-filament::export.formats.pdf'),
        };
    }
}
