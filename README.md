# Advanced Table Export for Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/occtherapist/advanced-table-export-for-filament.svg?style=flat-square)](https://packagist.org/packages/occtherapist/advanced-table-export-for-filament)
[![Total Downloads](https://img.shields.io/packagist/dt/occtherapist/advanced-table-export-for-filament.svg?style=flat-square)](https://packagist.org/packages/occtherapist/advanced-table-export-for-filament)
[![License](https://img.shields.io/github/license/OccTherapist/advanced-table-export-for-filament?style=flat-square)](LICENSE)
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

Publish Blade views for PDF and preview customization (optional):

```bash
php artisan vendor:publish --tag=advanced-table-export-for-filament-views
```

This copies templates to `resources/views/vendor/advanced-table-export-for-filament/`. Published views override the package defaults automatically.

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

Many `disable*` options from the original package map directly to config keys (see [Configuration](#configuration)) or can be set per action via method chaining (see [Action customization](#action-customization)).

---

## Action customization

Both export actions support a fluent API similar to `filament-export`:

```php
use Filament\Tables\Columns\TextColumn;
use OccTherapist\AdvancedTableExportForFilament\Actions\TableExportHeaderAction;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;

TableExportHeaderAction::make()
    ->fileName('monthly-report')
    ->timeFormat('Y-m-d')
    ->defaultFormat(ExportFormat::Pdf)
    ->defaultPageOrientation('portrait')
    ->disableCsv()
    ->disablePreview()
    ->directDownload()
    ->withHiddenColumns()
    ->csvDelimiter(';')
    ->withColumns([
        TextColumn::make('internal_notes')->label('Notes'),
    ])
    ->formatStates([
        'status' => fn ($record) => strtoupper((string) $record->status),
    ])
    ->extraViewData([
        'company' => 'Acme GmbH',
    ])
    ->fileNameFieldLabel('Report name')
    ->formatFieldLabel('Export as')
    ->filterColumnsFieldLabel('Included columns');
```

| Method | Description |
|--------|-------------|
| `fileName()` | Default file name |
| `timeFormat()` | Timestamp format when the file name is generated automatically |
| `disablePdf()` / `disableXlsx()` / `disableCsv()` | Hide export formats |
| `defaultFormat()` | Default selected format |
| `defaultPageOrientation()` | Default PDF orientation |
| `directDownload()` | Skip the modal and export immediately with defaults |
| `disableFilterColumns()` | Hide the column picker |
| `disableFileName()` | Hide the file name input |
| `disableFileNamePrefix()` | Disable automatic table-name prefix |
| `disablePreview()` | Hide the paginated preview |
| `disableTableColumns()` | Export only columns from `withColumns()` |
| `withHiddenColumns()` | Include toggled-hidden table columns in exports |
| `withColumns()` | Add extra model columns to the export |
| `csvDelimiter()` | CSV delimiter for this action |
| `formatStates()` | Override exported values per column |
| `extraViewData()` | Extra variables for PDF/preview Blade views |
| `modifyExportQueryUsing()` | Modify the export query before fetching records |
| `modifyPdfHtml()` | Modify rendered PDF HTML before conversion (all PDF drivers) |
| `modifyDompdfWriter()` | Customize the Dompdf instance when using the `dompdf` driver |
| `modifyXlsxWriter()` | Customize the OpenSpout XLSX writer (sheet name, etc.) |
| `modifyCsvWriter()` | Customize OpenSpout CSV options (delimiter, BOM) before export |

Action-level settings override global config values.

### Writer hooks

```php
use OpenSpout\Writer\CSV\Options;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

TableExportHeaderAction::make()
    ->modifyPdfHtml(fn (string $html, array $context): string => $html.'<footer>Okidoki</footer>')
    ->modifyDompdfWriter(fn (\Dompdf\Dompdf $dompdf, array $context) => $dompdf->setPaper('A4', 'landscape'))
    ->modifyXlsxWriter(fn (XlsxWriter $writer, array $context) => $writer->getCurrentSheet()->setName('Export'))
    ->modifyCsvWriter(fn (Options $options, array $context) => $options->FIELD_DELIMITER = '|');
```

Each hook receives a `$context` array: `fileName`, `headers`, `rows`, `rowCount`, `format`, and `orientation` (PDF only).

### Custom Blade views

```bash
php artisan vendor:publish --tag=advanced-table-export-for-filament-views
```

Edit `resources/views/vendor/advanced-table-export-for-filament/pdf/table.blade.php` or `export-preview.blade.php`. Combine with `extraViewData()` to pass custom variables into the templates.

---

## Roadmap

| Version | Focus |
|---------|-------|
| **v0.4.0** *(current)* | Publishable views, writer hooks (`modifyPdfHtml`, `modifyDompdfWriter`, `modifyXlsxWriter`, `modifyCsvWriter`) |
| **v0.x** | Further 0.x releases as needed |
| **v1.0** | API freeze + hardening when production checklist is green (see below) |
| **v1.1+** | End-user additional-columns UI |

### Path to v1.0

v1.0 ships when all criteria are met — not as the immediate next release:

1. Feature parity for your production use case (documented gaps OK)
2. At least one production project stable for 4+ weeks without vendor patches
3. Filament plugin listing approved
4. Test suite covers export pipeline and hooks
5. Upgrade guide from `filament-export` in README

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
