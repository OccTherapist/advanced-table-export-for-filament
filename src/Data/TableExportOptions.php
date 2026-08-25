<?php

namespace OccTherapist\AdvancedTableExportForFilament\Data;

use Closure;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use OccTherapist\AdvancedTableExportForFilament\Enums\ClipboardFormat;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Enums\JsonStructure;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportColumnCollection;

readonly class TableExportOptions
{
    /**
     * @param  array<int, Column>  $additionalColumns
     * @param  array<int, ExportFormat>|null  $formats
     * @param  array<string, Closure>  $formatStates
     * @param  array<string, mixed>  $extraViewData
     * @param  array<string, string>  $formatIcons
     * @param  array<string, string>  $formatLabels
     */
    public function __construct(
        public bool $usesSelectedRecords,
        public array $additionalColumns,
        public ?Closure $modifyExportQueryUsing,
        public int $maxPdfRows,
        public int $maxExportRows,
        public int $maxClipboardRows,
        public int $previewPerPage,
        public bool $disablePdf,
        public bool $disableXlsx,
        public bool $disableCsv,
        public bool $disableDocx,
        public bool $disableJson,
        public bool $disableXml,
        public bool $disableClipboard,
        public ?array $formats,
        public ExportFormat $defaultFormat,
        public string $defaultPageOrientation,
        public bool $disableFilterColumns,
        public bool $disableFileName,
        public bool $disableFileNamePrefix,
        public bool $disablePreview,
        public bool $disableTableColumns,
        public bool $includeHiddenColumns,
        public ?string $defaultFileName,
        public ?string $timeFormat,
        public ?string $csvDelimiter,
        public JsonStructure $jsonStructure,
        public bool $prettyJson,
        public string $xmlRoot,
        public string $xmlRowTag,
        public ClipboardFormat $clipboardFormat,
        public array $formatStates,
        public array $extraViewData,
        public ?string $fileNameFieldLabel,
        public ?string $formatFieldLabel,
        public ?string $pageOrientationFieldLabel,
        public ?string $filterColumnsFieldLabel,
        public ?string $groupLabel,
        public array $formatIcons,
        public array $formatLabels,
        public ?Closure $modifyPdfHtml = null,
        public ?Closure $modifyDompdfWriter = null,
        public ?Closure $modifyXlsxWriter = null,
        public ?Closure $modifyCsvWriter = null,
        public ?Closure $modifyJsonExport = null,
        public ?Closure $modifyXmlExport = null,
        public ?Closure $modifyDocxDocument = null,
    ) {}

    /**
     * @return array<int, ExportFormat>
     */
    public function availableFormats(): array
    {
        $candidates = $this->formats ?? ExportFormat::defaults();

        return array_values(array_filter(
            $candidates,
            fn (ExportFormat $format): bool => ! $this->isFormatDisabled($format),
        ));
    }

    /**
     * @return array<string, string>
     */
    public function availableFormatOptions(): array
    {
        $options = [];

        foreach ($this->availableFormats() as $format) {
            $options[$format->value] = $this->resolveFormatLabel($format);
        }

        return $options;
    }

    public function isFormatDisabled(ExportFormat $format): bool
    {
        return match ($format) {
            ExportFormat::Csv => $this->disableCsv,
            ExportFormat::Xlsx => $this->disableXlsx,
            ExportFormat::Pdf => $this->disablePdf,
            ExportFormat::Docx => $this->disableDocx,
            ExportFormat::Json => $this->disableJson,
            ExportFormat::Xml => $this->disableXml,
            ExportFormat::Clipboard => $this->disableClipboard,
        };
    }

    public function resolveFormat(?string $format): ExportFormat
    {
        $requested = ExportFormat::tryFrom((string) $format);

        if ($requested !== null && ! $this->isFormatDisabled($requested) && $this->isFormatAllowed($requested)) {
            return $requested;
        }

        if (! $this->isFormatDisabled($this->defaultFormat) && $this->isFormatAllowed($this->defaultFormat)) {
            return $this->defaultFormat;
        }

        foreach ($this->availableFormats() as $candidate) {
            return $candidate;
        }

        return ExportFormat::Xlsx;
    }

    public function resolveFormatLabel(ExportFormat $format): string
    {
        return $this->formatLabels[$format->value] ?? $format->getLabel();
    }

    public function resolveFormatIcon(ExportFormat $format): string
    {
        return $this->formatIcons[$format->value] ?? $format->getDefaultIcon();
    }

    public function resolveGroupLabel(): string
    {
        return $this->groupLabel ?? __('advanced-table-export-for-filament::export.group_label');
    }

    public function resolveRowLimit(ExportFormat $format): int
    {
        return match ($format) {
            ExportFormat::Pdf => $this->maxPdfRows,
            ExportFormat::Clipboard => $this->maxClipboardRows,
            default => $this->maxExportRows,
        };
    }

    /**
     * @return array<int, string>
     */
    public function resolveDefaultEnabledColumnNames(Table $table): array
    {
        if ($this->disableTableColumns) {
            return collect($this->additionalColumns)
                ->map(fn (Column $column): string => $column->getName())
                ->all();
        }

        return ExportColumnCollection::resolve(
            table: $table,
            additionalColumns: $this->additionalColumns,
            enabledColumnNames: null,
            includeHiddenColumns: $this->includeHiddenColumns,
            disableTableColumns: false,
        )
            ->map(fn (Column $column): string => $column->getName())
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function resolvePickerColumnNames(Table $table): array
    {
        return ExportColumnCollection::resolve(
            table: $table,
            additionalColumns: $this->additionalColumns,
            enabledColumnNames: null,
            includeHiddenColumns: true,
            disableTableColumns: false,
        )
            ->map(fn (Column $column): string => $column->getName())
            ->all();
    }

    public function getTimeFormat(): string
    {
        return $this->timeFormat ?? config('advanced-table-export-for-filament.time_format', 'M_d_Y-H_i');
    }

    public function getCsvDelimiter(): string
    {
        return $this->csvDelimiter ?? (string) config('advanced-table-export-for-filament.csv_delimiter', ',');
    }

    protected function isFormatAllowed(ExportFormat $format): bool
    {
        if ($this->formats === null) {
            return true;
        }

        return in_array($format, $this->formats, true);
    }
}
