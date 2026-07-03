<?php

namespace OccTherapist\AdvancedTableExportForFilament\Enums;

use Filament\Support\Contracts\HasLabel;

enum ClipboardFormat: string implements HasLabel
{
    case Tsv = 'tsv';
    case Csv = 'csv';
    case Json = 'json';

    public function getLabel(): string
    {
        return match ($this) {
            self::Tsv => __('advanced-table-export-for-filament::export.clipboard_formats.tsv'),
            self::Csv => __('advanced-table-export-for-filament::export.clipboard_formats.csv'),
            self::Json => __('advanced-table-export-for-filament::export.clipboard_formats.json'),
        };
    }
}
