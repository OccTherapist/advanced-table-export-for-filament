# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-08-25

### Added
- **Word (.docx)** export format via optional [`phpoffice/phpword`](https://github.com/PHPOffice/PHPWord)
- `disableDocx()` and `modifyDocxDocument()` writer hook
- Page orientation control for DOCX (shared with PDF in the export modal)

### Changed
- All seven export formats are enabled by default (CSV, XLSX, PDF, DOCX, JSON, XML, clipboard)

## [1.0.1] - 2026-07-03

### Added
- README banner art with light/dark theme support (`art/banner-*.png`)

### Changed
- Declare Laravel 13 compatibility in README and `composer.json` constraints

## [1.0.0] - 2026-07-03

### Added
- **JSON, XML, and clipboard** export formats
- **Quick export actions** via `TableExportQuickHeaderAction` and `TableExportQuickBulkAction` (ActionGroup with one action per format)
- `formats()` whitelist API on actions and the panel plugin
- `disableJson()`, `disableXml()`, and `disableClipboard()`
- `clipboardFormat()` with `ClipboardFormat` enum (`Tsv`, `Csv`, `Json`)
- `jsonStructure()` with `JsonStructure` enum (`Flat`, `Wrapped`) and `prettyJson()` / `compactJson()`
- `xmlRoot()` and `xmlRowTag()` for XML structure customization
- `formatIcon()`, `formatLabel()`, and `groupLabel()` for quick export presentation
- `modifyJsonExport()` and `modifyXmlExport()` writer hooks
- `maxClipboardRows()` on the panel plugin (default: 500)
- Clipboard copy fallback listener for Livewire dispatch

### Changed
- **Breaking:** all six export formats are enabled by default (CSV, XLSX, PDF, JSON, XML, clipboard)
- Unified export action label: **Export**
- Plugin and config can now define default formats, JSON/XML settings, and clipboard format

### Upgrade from 0.4.x
```php
TableExportHeaderAction::make()
    ->formats([
        ExportFormat::Csv,
        ExportFormat::Xlsx,
        ExportFormat::Pdf,
    ]);
```

## [0.4.0] - 2026-06-19

### Added
- Publishable Blade views via `advanced-table-export-for-filament-views` tag (PDF table + export preview)
- `modifyPdfHtml()` hook for driver-agnostic PDF HTML customization
- `modifyDompdfWriter()` hook when using the `dompdf` PDF driver
- `modifyXlsxWriter()` hook for OpenSpout XLSX writer customization
- `modifyCsvWriter()` hook for OpenSpout CSV options (delimiter, BOM)
- Standardized `$context` array passed to all writer hooks
- Roadmap section documenting the path to v1.0

## [0.3.0] - 2026-06-19

### Added
- Fluent action API compatible with `filament-export`: `disablePdf()`, `disableXlsx()`, `disableCsv()`, `defaultFormat()`, `defaultPageOrientation()`, `directDownload()`, `fileName()`, `timeFormat()`, `csvDelimiter()`, `withHiddenColumns()`, `disableTableColumns()`, `formatStates()`, `extraViewData()`, and field label methods
- `TableExportOptions` value object passed through the export pipeline
- `formatStates()` overrides for per-column export formatting
- `extraViewData()` for PDF and preview Blade templates

### Changed
- Action-level settings override global config (config remains the default fallback)
- `disableTableColumns()` exports only columns from `withColumns()`

## [0.2.1] - 2026-06-19

### Fixed
- Resolve panel plugin limits using the plugin ID instead of the class name

## [0.2.0] - 2026-06-19

### Added
- CSV, XLSX, and PDF export execution via `TableExportCoordinator`
- Paginated export preview in the modal
- Column resolution from table state (including hidden columns when selected)
- OpenSpout-based CSV/XLSX streaming downloads
- PDF table rendering via configurable `PdfRenderer` drivers

### Changed
- Export modal is fully functional (replaces v0.1.0 stub)

## [0.1.0] - 2026-06-19

### Added
- Filament v4/v5 plugin with panel-level row limits and preview settings
- `TableExportHeaderAction` for exporting filtered/sorted table data
- `TableExportBulkAction` for exporting selected records
- Export modal with format, orientation, filename, and column picker UI
- `ExportFormat` enum (CSV, XLSX, PDF)
- Pluggable PDF renderer contract with Sidecar, Browsershot, Dompdf, and null drivers
- Configuration file with sensible defaults
- English and German translations
- OpenSpout dependency for upcoming spreadsheet exports

[Unreleased]: https://github.com/OccTherapist/advanced-table-export-for-filament/compare/v1.0.1...HEAD
[1.0.1]: https://github.com/OccTherapist/advanced-table-export-for-filament/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/OccTherapist/advanced-table-export-for-filament/compare/v0.4.0...v1.0.0
[0.4.0]: https://github.com/OccTherapist/advanced-table-export-for-filament/releases/tag/v0.4.0
[0.3.0]: https://github.com/OccTherapist/advanced-table-export-for-filament/releases/tag/v0.3.0
[0.2.1]: https://github.com/OccTherapist/advanced-table-export-for-filament/releases/tag/v0.2.1
[0.2.0]: https://github.com/OccTherapist/advanced-table-export-for-filament/releases/tag/v0.2.0
[0.1.0]: https://github.com/OccTherapist/advanced-table-export-for-filament/releases/tag/v0.1.0
