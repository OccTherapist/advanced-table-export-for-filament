<?php

namespace OccTherapist\AdvancedTableExportForFilament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use OccTherapist\AdvancedTableExportForFilament\Concerns\InteractsWithAdvancedTableExportPlugin;

class AdvancedTableExportForFilamentPlugin implements Plugin
{
    use InteractsWithAdvancedTableExportPlugin;

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'advanced-table-export-for-filament';
    }

    public function register(Panel $panel): void
    {
        $this->maxPdfRows = (int) config('advanced-table-export-for-filament.max_pdf_rows', $this->maxPdfRows);
        $this->maxExportRows = (int) config('advanced-table-export-for-filament.max_export_rows', $this->maxExportRows);
        $this->previewPerPage = (int) config('advanced-table-export-for-filament.preview_per_page', $this->previewPerPage);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
