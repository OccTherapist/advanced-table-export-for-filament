<?php

namespace OccTherapist\AdvancedTableExportForFilament\Enums;

use Filament\Support\Contracts\HasLabel;

enum ExportFormat: string implements HasLabel
{
    case Csv = 'csv';
    case Xlsx = 'xlsx';
    case Pdf = 'pdf';
    case Docx = 'docx';
    case Json = 'json';
    case Xml = 'xml';
    case Clipboard = 'clipboard';

    public function getLabel(): string
    {
        return match ($this) {
            self::Csv => __('advanced-table-export-for-filament::export.formats.csv'),
            self::Xlsx => __('advanced-table-export-for-filament::export.formats.xlsx'),
            self::Pdf => __('advanced-table-export-for-filament::export.formats.pdf'),
            self::Docx => __('advanced-table-export-for-filament::export.formats.docx'),
            self::Json => __('advanced-table-export-for-filament::export.formats.json'),
            self::Xml => __('advanced-table-export-for-filament::export.formats.xml'),
            self::Clipboard => __('advanced-table-export-for-filament::export.formats.clipboard'),
        };
    }

    public function getDefaultIcon(): string
    {
        return match ($this) {
            self::Csv => 'heroicon-o-document-text',
            self::Xlsx => 'heroicon-o-table-cells',
            self::Pdf => 'heroicon-o-document',
            self::Docx => 'heroicon-o-document-duplicate',
            self::Json => 'heroicon-o-code-bracket',
            self::Xml => 'heroicon-o-code-bracket-square',
            self::Clipboard => 'heroicon-o-clipboard-document',
        };
    }

    public function downloadsFile(): bool
    {
        return $this !== self::Clipboard;
    }

    /**
     * @return array<int, self>
     */
    public static function defaults(): array
    {
        return self::cases();
    }
}
