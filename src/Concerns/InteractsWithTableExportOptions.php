<?php

namespace OccTherapist\AdvancedTableExportForFilament\Concerns;

use Closure;
use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use OccTherapist\AdvancedTableExportForFilament\AdvancedTableExportForFilamentPlugin;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ClipboardFormat;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Enums\JsonStructure;

trait InteractsWithTableExportOptions
{
    /** @var array<int, Column> */
    protected array $additionalExportColumns = [];

    protected ?Closure $modifyExportQueryUsing = null;

    protected ?string $defaultFileName = null;

    protected ?string $timeFormat = null;

    protected ?bool $disablePdf = null;

    protected ?bool $disableXlsx = null;

    protected ?bool $disableCsv = null;

    protected ?bool $disableJson = null;

    protected ?bool $disableXml = null;

    protected ?bool $disableClipboard = null;

    /** @var array<int, ExportFormat>|null */
    protected ?array $formats = null;

    protected ExportFormat|string|null $defaultFormat = null;

    protected ?string $defaultPageOrientation = null;

    protected bool $directDownload = false;

    protected ?bool $disableFilterColumns = null;

    protected ?bool $disableFileName = null;

    protected ?bool $disableFileNamePrefix = null;

    protected ?bool $disablePreview = null;

    protected ?bool $disableTableColumns = null;

    protected ?bool $includeHiddenColumns = null;

    protected ?string $csvDelimiter = null;

    protected ?JsonStructure $jsonStructure = null;

    protected ?bool $prettyJson = null;

    protected ?string $xmlRoot = null;

    protected ?string $xmlRowTag = null;

    protected ?ClipboardFormat $clipboardFormat = null;

    protected ?string $groupLabel = null;

    /** @var array<string, string> */
    protected array $formatIcons = [];

    /** @var array<string, string> */
    protected array $formatLabels = [];

    /** @var array<string, Closure> */
    protected array $formatStates = [];

    /** @var array<string, mixed>|Closure|null */
    protected array|Closure|null $extraViewData = null;

    protected ?string $fileNameFieldLabel = null;

    protected ?string $formatFieldLabel = null;

    protected ?string $pageOrientationFieldLabel = null;

    protected ?string $filterColumnsFieldLabel = null;

    protected ?Closure $modifyPdfHtml = null;

    protected ?Closure $modifyDompdfWriter = null;

    protected ?Closure $modifyXlsxWriter = null;

    protected ?Closure $modifyCsvWriter = null;

    protected ?Closure $modifyJsonExport = null;

    protected ?Closure $modifyXmlExport = null;

    /**
     * @param  array<int, Column>  $columns
     */
    public function withColumns(array $columns): static
    {
        $this->additionalExportColumns = $columns;

        return $this;
    }

    public function modifyExportQueryUsing(?Closure $callback): static
    {
        $this->modifyExportQueryUsing = $callback;

        return $this;
    }

    public function fileName(?string $fileName): static
    {
        $this->defaultFileName = $fileName;

        return $this;
    }

    public function timeFormat(?string $format): static
    {
        $this->timeFormat = $format;

        return $this;
    }

    /**
     * @param  array<int, ExportFormat>  $formats
     */
    public function formats(array $formats): static
    {
        if ($formats === []) {
            throw new InvalidArgumentException('At least one export format must be specified.');
        }

        $this->formats = $formats;

        return $this;
    }

    public function disablePdf(bool $condition = true): static
    {
        $this->disablePdf = $condition;

        return $this;
    }

    public function disableXlsx(bool $condition = true): static
    {
        $this->disableXlsx = $condition;

        return $this;
    }

    public function disableCsv(bool $condition = true): static
    {
        $this->disableCsv = $condition;

        return $this;
    }

    public function disableJson(bool $condition = true): static
    {
        $this->disableJson = $condition;

        return $this;
    }

    public function disableXml(bool $condition = true): static
    {
        $this->disableXml = $condition;

        return $this;
    }

    public function disableClipboard(bool $condition = true): static
    {
        $this->disableClipboard = $condition;

        return $this;
    }

    public function defaultFormat(ExportFormat|string $format): static
    {
        $this->defaultFormat = $format;

        return $this;
    }

    public function defaultPageOrientation(string $orientation): static
    {
        $this->defaultPageOrientation = $orientation;

        return $this;
    }

    public function directDownload(bool $condition = true): static
    {
        $this->directDownload = $condition;

        return $this;
    }

    public function disableFilterColumns(bool $condition = true): static
    {
        $this->disableFilterColumns = $condition;

        return $this;
    }

    public function disableFileName(bool $condition = true): static
    {
        $this->disableFileName = $condition;

        return $this;
    }

    public function disableFileNamePrefix(bool $condition = true): static
    {
        $this->disableFileNamePrefix = $condition;

        return $this;
    }

    public function disablePreview(bool $condition = true): static
    {
        $this->disablePreview = $condition;

        return $this;
    }

    public function disableTableColumns(bool $condition = true): static
    {
        $this->disableTableColumns = $condition;

        return $this;
    }

    public function withHiddenColumns(bool $condition = true): static
    {
        $this->includeHiddenColumns = $condition;

        return $this;
    }

    public function csvDelimiter(?string $delimiter): static
    {
        $this->csvDelimiter = $delimiter;

        return $this;
    }

    public function jsonStructure(JsonStructure $structure): static
    {
        $this->jsonStructure = $structure;

        return $this;
    }

    public function prettyJson(bool $condition = true): static
    {
        $this->prettyJson = $condition;

        return $this;
    }

    public function compactJson(bool $condition = true): static
    {
        return $this->prettyJson(! $condition);
    }

    public function xmlRoot(string $root): static
    {
        $this->xmlRoot = $root;

        return $this;
    }

    public function xmlRowTag(string $rowTag): static
    {
        $this->xmlRowTag = $rowTag;

        return $this;
    }

    public function clipboardFormat(ClipboardFormat $format): static
    {
        $this->clipboardFormat = $format;

        return $this;
    }

    public function groupLabel(?string $label): static
    {
        $this->groupLabel = $label;

        return $this;
    }

    public function formatIcon(ExportFormat $format, string $icon): static
    {
        $this->formatIcons[$format->value] = $icon;

        return $this;
    }

    public function formatLabel(ExportFormat $format, string $label): static
    {
        $this->formatLabels[$format->value] = $label;

        return $this;
    }

    /**
     * @param  array<string, Closure(Model): mixed>  $formatStates
     */
    public function formatStates(array $formatStates): static
    {
        $this->formatStates = $formatStates;

        return $this;
    }

    /**
     * @param  array<string, mixed>|Closure  $data
     */
    public function extraViewData(array|Closure $data): static
    {
        $this->extraViewData = $data;

        return $this;
    }

    public function fileNameFieldLabel(?string $label): static
    {
        $this->fileNameFieldLabel = $label;

        return $this;
    }

    public function formatFieldLabel(?string $label): static
    {
        $this->formatFieldLabel = $label;

        return $this;
    }

    public function pageOrientationFieldLabel(?string $label): static
    {
        $this->pageOrientationFieldLabel = $label;

        return $this;
    }

    public function filterColumnsFieldLabel(?string $label): static
    {
        $this->filterColumnsFieldLabel = $label;

        return $this;
    }

    public function modifyPdfHtml(?Closure $callback): static
    {
        $this->modifyPdfHtml = $callback;

        return $this;
    }

    public function modifyDompdfWriter(?Closure $callback): static
    {
        $this->modifyDompdfWriter = $callback;

        return $this;
    }

    public function modifyXlsxWriter(?Closure $callback): static
    {
        $this->modifyXlsxWriter = $callback;

        return $this;
    }

    public function modifyCsvWriter(?Closure $callback): static
    {
        $this->modifyCsvWriter = $callback;

        return $this;
    }

    public function modifyJsonExport(?Closure $callback): static
    {
        $this->modifyJsonExport = $callback;

        return $this;
    }

    public function modifyXmlExport(?Closure $callback): static
    {
        $this->modifyXmlExport = $callback;

        return $this;
    }

    public function shouldDirectDownload(): bool
    {
        return $this->directDownload;
    }

    public function getTableExportOptions(bool $usesSelectedRecords): TableExportOptions
    {
        $plugin = $this->resolvePlugin();

        $defaultFormat = $this->defaultFormat instanceof ExportFormat
            ? $this->defaultFormat
            : ExportFormat::tryFrom((string) ($this->defaultFormat ?? config('advanced-table-export-for-filament.default_format', ExportFormat::Xlsx->value)))
                ?? ExportFormat::Xlsx;

        return new TableExportOptions(
            usesSelectedRecords: $usesSelectedRecords,
            additionalColumns: $this->additionalExportColumns,
            modifyExportQueryUsing: $this->modifyExportQueryUsing,
            maxPdfRows: $this->getMaxPdfRows(),
            maxExportRows: $this->getMaxExportRows(),
            maxClipboardRows: $this->getMaxClipboardRows(),
            previewPerPage: $this->getPreviewPerPage(),
            disablePdf: $this->disablePdf ?? false,
            disableXlsx: $this->disableXlsx ?? false,
            disableCsv: $this->disableCsv ?? false,
            disableJson: $this->disableJson ?? false,
            disableXml: $this->disableXml ?? false,
            disableClipboard: $this->disableClipboard ?? false,
            formats: $this->resolveConfiguredFormats($plugin),
            defaultFormat: $defaultFormat,
            defaultPageOrientation: $this->defaultPageOrientation
                ?? config('advanced-table-export-for-filament.default_page_orientation', 'landscape'),
            disableFilterColumns: $this->disableFilterColumns
                ?? config('advanced-table-export-for-filament.disable_filter_columns', false),
            disableFileName: $this->disableFileName
                ?? config('advanced-table-export-for-filament.disable_file_name', false),
            disablePreview: $this->disablePreview
                ?? config('advanced-table-export-for-filament.disable_preview', false),
            disableFileNamePrefix: $this->disableFileNamePrefix
                ?? config('advanced-table-export-for-filament.disable_file_name_prefix', false),
            disableTableColumns: $this->disableTableColumns ?? false,
            includeHiddenColumns: $this->includeHiddenColumns ?? false,
            defaultFileName: $this->defaultFileName,
            timeFormat: $this->timeFormat,
            csvDelimiter: $this->csvDelimiter,
            jsonStructure: $this->jsonStructure
                ?? $plugin?->getJsonStructure()
                ?? JsonStructure::tryFrom((string) config('advanced-table-export-for-filament.json_structure', JsonStructure::Flat->value))
                ?? JsonStructure::Flat,
            prettyJson: $this->prettyJson
                ?? $plugin?->getPrettyJson()
                ?? (bool) config('advanced-table-export-for-filament.pretty_json', true),
            xmlRoot: $this->xmlRoot
                ?? $plugin?->getXmlRoot()
                ?? (string) config('advanced-table-export-for-filament.xml_root', 'rows'),
            xmlRowTag: $this->xmlRowTag
                ?? $plugin?->getXmlRowTag()
                ?? (string) config('advanced-table-export-for-filament.xml_row_tag', 'row'),
            clipboardFormat: $this->clipboardFormat
                ?? $plugin?->getClipboardFormat()
                ?? ClipboardFormat::tryFrom((string) config('advanced-table-export-for-filament.clipboard_format', ClipboardFormat::Tsv->value))
                ?? ClipboardFormat::Tsv,
            formatStates: $this->formatStates,
            extraViewData: $this->resolveExtraViewData(),
            fileNameFieldLabel: $this->fileNameFieldLabel,
            formatFieldLabel: $this->formatFieldLabel,
            pageOrientationFieldLabel: $this->pageOrientationFieldLabel,
            filterColumnsFieldLabel: $this->filterColumnsFieldLabel,
            groupLabel: $this->groupLabel,
            formatIcons: $this->formatIcons,
            formatLabels: $this->formatLabels,
            modifyPdfHtml: $this->modifyPdfHtml,
            modifyDompdfWriter: $this->modifyDompdfWriter,
            modifyXlsxWriter: $this->modifyXlsxWriter,
            modifyCsvWriter: $this->modifyCsvWriter,
            modifyJsonExport: $this->modifyJsonExport,
            modifyXmlExport: $this->modifyXmlExport,
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveExtraViewData(): array
    {
        if ($this->extraViewData === null) {
            return [];
        }

        $data = $this->evaluate($this->extraViewData);

        return is_array($data) ? $data : [];
    }

    /**
     * @return array<int, ExportFormat>|null
     */
    protected function resolveConfiguredFormats(?AdvancedTableExportForFilamentPlugin $plugin): ?array
    {
        if ($this->formats !== null) {
            return $this->formats;
        }

        if ($plugin?->getFormats() !== null) {
            return $plugin->getFormats();
        }

        $configuredFormats = config('advanced-table-export-for-filament.formats');

        if (! is_array($configuredFormats)) {
            return null;
        }

        return array_values(array_filter(array_map(
            fn (mixed $format): ?ExportFormat => $format instanceof ExportFormat
                ? $format
                : ExportFormat::tryFrom((string) $format),
            $configuredFormats,
        )));
    }

    protected function resolvePlugin(): ?AdvancedTableExportForFilamentPlugin
    {
        $panel = filament()->getCurrentPanel();

        if (! $panel?->hasPlugin(AdvancedTableExportForFilamentPlugin::ID)) {
            return null;
        }

        $plugin = $panel->getPlugin(AdvancedTableExportForFilamentPlugin::ID);

        return $plugin instanceof AdvancedTableExportForFilamentPlugin ? $plugin : null;
    }

    protected function getMaxPdfRows(): int
    {
        $plugin = $this->resolvePlugin();

        if ($plugin !== null) {
            return $plugin->getMaxPdfRows();
        }

        return (int) config('advanced-table-export-for-filament.max_pdf_rows', 200);
    }

    protected function getMaxExportRows(): int
    {
        $plugin = $this->resolvePlugin();

        if ($plugin !== null) {
            return $plugin->getMaxExportRows();
        }

        return (int) config('advanced-table-export-for-filament.max_export_rows', 2000);
    }

    protected function getMaxClipboardRows(): int
    {
        $plugin = $this->resolvePlugin();

        if ($plugin !== null) {
            return $plugin->getMaxClipboardRows();
        }

        return (int) config('advanced-table-export-for-filament.max_clipboard_rows', 500);
    }

    protected function getPreviewPerPage(): int
    {
        $plugin = $this->resolvePlugin();

        if ($plugin !== null) {
            return $plugin->getPreviewPerPage();
        }

        return (int) config('advanced-table-export-for-filament.preview_per_page', 25);
    }
}
