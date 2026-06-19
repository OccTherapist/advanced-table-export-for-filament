# Advanced Table Export for Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/occtherapist/advanced-table-export-for-filament.svg?style=flat-square)](https://packagist.org/packages/occtherapist/advanced-table-export-for-filament)
[![Total Downloads](https://img.shields.io/packagist/dt/occtherapist/advanced-table-export-for-filament.svg?style=flat-square)](https://packagist.org/packages/occtherapist/advanced-table-export-for-filament)
[![License](https://img.shields.io/packagist/l/occtherapist/advanced-table-export-for-filament.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/occtherapist/advanced-table-export-for-filament.svg?style=flat-square)](https://packagist.org/packages/occtherapist/advanced-table-export-for-filament)

**Export and print Filament admin tables in seconds** — CSV, XLSX, and PDF with column selection, preview, and flexible PDF drivers.

Built for **Filament v4 and v5** on **Laravel 11/12**. A modern, actively maintained successor to the export workflow many teams relied on with [`alperenersoy/filament-export`](https://github.com/alperenersoy/filament-export).

---

## Why this package?

| Need | This package |
|------|--------------|
| Export **filtered, sorted, searched** table data (not just selected rows) | Header action exports the current table query |
| Export **only selected rows** | Bulk action for row selection |
| **CSV, XLSX, PDF** from one modal | Single action with format picker |
| **Choose columns** before export | Built-in column filter in the export modal |
| **PDF** with portrait or landscape | Configurable orientation per export |
| **Multiple PDF backends** | Sidecar, Browsershot, Dompdf, or null driver |
| **Filament v4/v5** without waiting on upstream | First-class support from day one |
| **German & English** UI | Translations included |

Filament's [native export action](https://filamentphp.com/docs/actions/export) is powerful for queued, notification-based exports with custom exporter classes. This package targets teams that want a **lightweight, modal-based export** directly from the table — similar to `filament-export`, but updated for current Filament APIs.

---

## Features

- **Header action** — export the full filtered/sorted table state
- **Bulk action** — export only selected records
- **Formats** — CSV, XLSX, PDF (print-ready)
- **Column picker** — let users choose which columns to include
- **Custom file names** — optional filename input with timestamp prefix
- **PDF orientation** — landscape or portrait
- **Pluggable PDF renderers** — Sidecar Browsershot, local Browsershot, Dompdf
- **Row limits** — configurable caps for PDF and spreadsheet exports
- **Panel plugin** — central limits and defaults per Filament panel
- **i18n** — English and German translations out of the box
- **Preview UI** — paginated preview in the export modal

---

## Requirements

- PHP 8.2+
- [Filament](https://filamentphp.com/) 4 or 5
- Laravel 11 or 12

**Optional** (pick one PDF stack):

| Driver | Packages |
|--------|----------|
| `sidecar` | `spatie/laravel-pdf`, `wnx/sidecar-browsershot` |
| `browsershot` | `spatie/laravel-pdf`, `spatie/browsershot` |
| `dompdf` | `dompdf/dompdf` |
| `null` | No PDF dependencies (default) |

Spreadsheet exports use [OpenSpout](https://github.com/openspout/openspout) (included).

---

## Installation

```bash
composer require occtherapist/advanced-table-export-for-filament
```

Register the plugin in your panel provider (e.g. `app/Providers/Filament/AdminPanelProvider.php`):

```php
use Filament\Panel;
use OccTherapist\AdvancedTableExportForFilament\AdvancedTableExportForFilamentPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            AdvancedTableExportForFilamentPlugin::make()
                ->maxPdfRows(200)
                ->maxExportRows(2000)
                ->previewPerPage(25),
        ]);
}
```

Add the actions to any table:

```php
use OccTherapist\AdvancedTableExportForFilament\Actions\TableExportBulkAction;
use OccTherapist\AdvancedTableExportForFilament\Actions\TableExportHeaderAction;
use Filament\Tables\Columns\TextColumn;

public function table(Table $table): Table
{
    return $table
        ->headerActions([
            TableExportHeaderAction::make()
                ->modifyExportQueryUsing(fn ($query) => $query->where('is_active', true)),
        ])
        ->toolbarActions([
            TableExportBulkAction::make()
                ->withColumns([
                    TextColumn::make('internal_notes')->label('Notes'),
                ]),
        ]);
}
```

`TableExportHeaderAction` exports the filtered, sorted, and searched table query. `TableExportBulkAction` exports only the selected rows. Use `withColumns()` to include extra model columns that are not visible in the table.

Publish the config (optional):

```bash
php artisan vendor:publish --tag=advanced-table-export-for-filament-config
```

Set your PDF driver in `.env`:

```env
ADVANCED_TABLE_EXPORT_PDF_RENDERER=sidecar
```

---

## Configuration

All options live in `config/advanced-table-export-for-filament.php`:

| Key | Default | Description |
|-----|---------|-------------|
| `default_format` | `xlsx` | Default export format |
| `default_page_orientation` | `landscape` | PDF orientation |
| `time_format` | `M_d_Y-H_i` | Timestamp suffix for generated filenames |
| `max_pdf_rows` | `200` | Max rows for PDF exports |
| `max_export_rows` | `2000` | Max rows for CSV/XLSX exports |
| `csv_delimiter` | `,` | CSV field delimiter |
| `pdf_renderer` | `null` | PDF driver: `sidecar`, `browsershot`, `dompdf`, `null` |
| `disable_preview` | `false` | Hide preview section in modal |
| `disable_filter_columns` | `false` | Hide column picker |
| `disable_file_name` | `false` | Hide filename input |
| `disable_file_name_prefix` | `false` | Disable timestamp prefix on filenames |
| `disable_additional_columns` | `false` | Disable extra column inputs |
| `preview_per_page` | `25` | Preview pagination size (v0.2.0) |
| `action_icon` | `heroicon-o-arrow-down-on-square` | Action button icon |

Panel-level limits override config when set on the plugin:

```php
AdvancedTableExportForFilamentPlugin::make()
    ->maxPdfRows(500)
    ->maxExportRows(10000)
    ->previewPerPage(50);
```

---

## PDF rendering

The package resolves a `PdfRenderer` contract from the container based on `pdf_renderer`:

```php
// config/advanced-table-export-for-filament.php
'pdf_renderer' => env('ADVANCED_TABLE_EXPORT_PDF_RENDERER', 'null'),
```

| Driver | Best for |
|--------|----------|
| **sidecar** | Production on AWS Lambda via [Sidecar Browsershot](https://github.com/wnx/laravel-sidecar-browsershot) |
| **browsershot** | Local/dev with Chrome via [Browsershot](https://github.com/spatie/browsershot) |
| **dompdf** | Simple HTML-to-PDF without a headless browser |
| **null** | Development until PDF export ships in v0.2.0 |

---

## Migrating from `filament-export`

If you used [`alperenersoy/filament-export`](https://github.com/alperenersoy/filament-export) on Filament v2/v3, this package offers a familiar workflow for Filament v4/v5:

```diff
- use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
- use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
+ use OccTherapist\AdvancedTableExportForFilament\Actions\TableExportHeaderAction;
+ use OccTherapist\AdvancedTableExportForFilament\Actions\TableExportBulkAction;

  ->headerActions([
-     FilamentExportHeaderAction::make('export'),
+     TableExportHeaderAction::make(),
  ])
  ->toolbarActions([
-     FilamentExportBulkAction::make('export'),
+     TableExportBulkAction::make(),
  ])
```

Many `disable*` options from the original package map directly to config keys (see [Configuration](#configuration)). Method chaining for labels and format toggles will expand in upcoming releases — [open an issue](https://github.com/OccTherapist/advanced-table-export-for-filament/issues) if you need a specific API.

---

## Roadmap

| Version | Focus |
|---------|-------|
| **v0.2.0** *(current)* | CSV/XLSX/PDF export execution, paginated preview, column resolution |
| **v0.3.0** | Action fluent API (`disablePdf()`, `withColumns()`, `directDownload()`, …) |

Star or watch the repository to follow progress: [github.com/OccTherapist/advanced-table-export-for-filament](https://github.com/OccTherapist/advanced-table-export-for-filament)

---

## Contributing

Contributions are welcome! See [CONTRIBUTING.md](CONTRIBUTING.md) for setup and guidelines.

Found a bug or missing feature? [Open an issue](https://github.com/OccTherapist/advanced-table-export-for-filament/issues).

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release notes.

---

## License

MIT © [Igor Clauss](https://igorclauss.de)

---

## Author

**Igor Clauss** — Laravel & Filament developer

- Website: [igorclauss.de](https://igorclauss.de)
- GitHub: [@OccTherapist](https://github.com/OccTherapist)

Built with care for teams who need reliable table exports in modern Filament panels.
