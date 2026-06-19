# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/OccTherapist/advanced-table-export-for-filament/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/OccTherapist/advanced-table-export-for-filament/releases/tag/v0.2.0
[0.1.0]: https://github.com/OccTherapist/advanced-table-export-for-filament/releases/tag/v0.1.0
