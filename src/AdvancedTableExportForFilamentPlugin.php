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
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
