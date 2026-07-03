<?php

namespace OccTherapist\AdvancedTableExportForFilament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use OccTherapist\AdvancedTableExportForFilament\Concerns\InteractsWithAdvancedTableExportPlugin;
use OccTherapist\AdvancedTableExportForFilament\Enums\ClipboardFormat;
use OccTherapist\AdvancedTableExportForFilament\Enums\JsonStructure;

class AdvancedTableExportForFilamentPlugin implements Plugin
{
    use InteractsWithAdvancedTableExportPlugin;

    public const ID = 'advanced-table-export-for-filament';

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function register(Panel $panel): void
    {
        $this->maxPdfRows = (int) config('advanced-table-export-for-filament.max_pdf_rows', $this->maxPdfRows);
        $this->maxExportRows = (int) config('advanced-table-export-for-filament.max_export_rows', $this->maxExportRows);
        $this->maxClipboardRows = (int) config('advanced-table-export-for-filament.max_clipboard_rows', $this->maxClipboardRows);
        $this->previewPerPage = (int) config('advanced-table-export-for-filament.preview_per_page', $this->previewPerPage);
        $this->prettyJson = (bool) config('advanced-table-export-for-filament.pretty_json', true);
        $this->jsonStructure = JsonStructure::tryFrom((string) config('advanced-table-export-for-filament.json_structure', JsonStructure::Flat->value))
            ?? JsonStructure::Flat;
        $this->clipboardFormat = ClipboardFormat::tryFrom((string) config('advanced-table-export-for-filament.clipboard_format', ClipboardFormat::Tsv->value))
            ?? ClipboardFormat::Tsv;
        $this->xmlRoot = (string) config('advanced-table-export-for-filament.xml_root', 'rows');
        $this->xmlRowTag = (string) config('advanced-table-export-for-filament.xml_row_tag', 'row');
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
