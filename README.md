# Advanced Table Export for Filament

Customizable export and print functionality for Filament admin tables — a Filament v4/v5 compatible successor to the export + print workflow many teams used with `alperenersoy/filament-export`.

## Status

**v0.1.0** — package scaffolding, plugin registration, actions, PDF driver interface, and configuration. Export execution ships in **v0.2.0**.

## Requirements

- PHP 8.2+
- Filament 4 or 5
- Laravel 11 or 12

Optional for PDF rendering:

- `spatie/laravel-pdf` + `wnx/sidecar-browsershot` (Sidecar)
- `spatie/laravel-pdf` + `spatie/browsershot` (local)
- `dompdf/dompdf`

## Installation

```bash
composer require occtherapist/advanced-table-export-for-filament
```

Register the plugin in your panel provider:

```php
use OccTherapist\AdvancedTableExportForFilament\AdvancedTableExportForFilamentPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            AdvancedTableExportForFilamentPlugin::make()
                ->maxPdfRows(200)
                ->maxExportRows(2000),
        ]);
}
```

Use the actions on a table:

```php
use OccTherapist\AdvancedTableExportForFilament\Actions\TableExportBulkAction;
use OccTherapist\AdvancedTableExportForFilament\Actions\TableExportHeaderAction;

->headerActions([
    TableExportHeaderAction::make(),
])
->toolbarActions([
    TableExportBulkAction::make(),
])
```

Publish the config:

```bash
php artisan vendor:publish --tag=advanced-table-export-for-filament-config
```

## Configuration

```env
ADVANCED_TABLE_EXPORT_PDF_RENDERER=sidecar
```

Supported drivers: `sidecar`, `browsershot`, `dompdf`, `null`.

## License

MIT © [Igor Clauss](https://github.com/OccTherapist)
