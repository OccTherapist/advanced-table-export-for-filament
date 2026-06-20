<?php

namespace OccTherapist\AdvancedTableExportForFilament\Concerns;

use Closure;
use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Model;
use OccTherapist\AdvancedTableExportForFilament\AdvancedTableExportForFilamentPlugin;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;

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

    public function shouldDirectDownload(): bool
    {
        return $this->directDownload;
    }

    public function getTableExportOptions(bool $usesSelectedRecords): TableExportOptions
    {
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
            previewPerPage: $this->getPreviewPerPage(),
            disablePdf: $this->disablePdf ?? false,
            disableXlsx: $this->disableXlsx ?? false,
            disableCsv: $this->disableCsv ?? false,
            defaultFormat: $defaultFormat,
            defaultPageOrientation: $this->defaultPageOrientation
                ?? config('advanced-table-export-for-filament.default_page_orientation', 'landscape'),
            disableFilterColumns: $this->disableFilterColumns
                ?? config('advanced-table-export-for-filament.disable_filter_columns', false),
            disableFileName: $this->disableFileName
                ?? config('advanced-table-export-for-filament.disable_file_name', false),
            disableFileNamePrefix: $this->disableFileNamePrefix
                ?? config('advanced-table-export-for-filament.disable_file_name_prefix', false),
            disablePreview: $this->disablePreview
                ?? config('advanced-table-export-for-filament.disable_preview', false),
            disableTableColumns: $this->disableTableColumns ?? false,
            includeHiddenColumns: $this->includeHiddenColumns ?? false,
            defaultFileName: $this->defaultFileName,
            timeFormat: $this->timeFormat,
            csvDelimiter: $this->csvDelimiter,
            formatStates: $this->formatStates,
            extraViewData: $this->resolveExtraViewData(),
            fileNameFieldLabel: $this->fileNameFieldLabel,
            formatFieldLabel: $this->formatFieldLabel,
            pageOrientationFieldLabel: $this->pageOrientationFieldLabel,
            filterColumnsFieldLabel: $this->filterColumnsFieldLabel,
            modifyPdfHtml: $this->modifyPdfHtml,
            modifyDompdfWriter: $this->modifyDompdfWriter,
            modifyXlsxWriter: $this->modifyXlsxWriter,
            modifyCsvWriter: $this->modifyCsvWriter,
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

    protected function getMaxPdfRows(): int
    {
        $panel = filament()->getCurrentPanel();

        if ($panel?->hasPlugin(AdvancedTableExportForFilamentPlugin::ID)) {
            $plugin = $panel->getPlugin(AdvancedTableExportForFilamentPlugin::ID);

            if ($plugin instanceof AdvancedTableExportForFilamentPlugin) {
                return $plugin->getMaxPdfRows();
            }
        }

        return (int) config('advanced-table-export-for-filament.max_pdf_rows', 200);
    }

    protected function getMaxExportRows(): int
    {
        $panel = filament()->getCurrentPanel();

        if ($panel?->hasPlugin(AdvancedTableExportForFilamentPlugin::ID)) {
            $plugin = $panel->getPlugin(AdvancedTableExportForFilamentPlugin::ID);

            if ($plugin instanceof AdvancedTableExportForFilamentPlugin) {
                return $plugin->getMaxExportRows();
            }
        }

        return (int) config('advanced-table-export-for-filament.max_export_rows', 2000);
    }

    protected function getPreviewPerPage(): int
    {
        $panel = filament()->getCurrentPanel();

        if ($panel?->hasPlugin(AdvancedTableExportForFilamentPlugin::ID)) {
            $plugin = $panel->getPlugin(AdvancedTableExportForFilamentPlugin::ID);

            if ($plugin instanceof AdvancedTableExportForFilamentPlugin) {
                return $plugin->getPreviewPerPage();
            }
        }

        return (int) config('advanced-table-export-for-filament.preview_per_page', 25);
    }
}
