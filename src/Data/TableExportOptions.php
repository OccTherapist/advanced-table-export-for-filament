<?php

namespace OccTherapist\AdvancedTableExportForFilament\Data;

use Closure;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportColumnCollection;

readonly class TableExportOptions
{
    /**
     * @param  array<int, Column>  $additionalColumns
     * @param  array<string, Closure>  $formatStates
     * @param  array<string, mixed>  $extraViewData
     */
    public function __construct(
        public bool $usesSelectedRecords,
        public array $additionalColumns,
        public ?Closure $modifyExportQueryUsing,
        public int $maxPdfRows,
        public int $maxExportRows,
        public int $previewPerPage,
        public bool $disablePdf,
        public bool $disableXlsx,
        public bool $disableCsv,
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
        public array $formatStates,
        public array $extraViewData,
        public ?string $fileNameFieldLabel,
        public ?string $formatFieldLabel,
        public ?string $pageOrientationFieldLabel,
        public ?string $filterColumnsFieldLabel,
    ) {}

    /**
     * @return array<string, string>
     */
    public function availableFormatOptions(): array
    {
        $options = [];

        foreach (ExportFormat::cases() as $format) {
            if ($this->isFormatDisabled($format)) {
                continue;
            }

            $options[$format->value] = $format->getLabel();
        }

        return $options;
    }

    public function isFormatDisabled(ExportFormat $format): bool
    {
        return match ($format) {
            ExportFormat::Csv => $this->disableCsv,
            ExportFormat::Xlsx => $this->disableXlsx,
            ExportFormat::Pdf => $this->disablePdf,
        };
    }

    public function resolveFormat(?string $format): ExportFormat
    {
        $requested = ExportFormat::tryFrom((string) $format);

        if ($requested !== null && ! $this->isFormatDisabled($requested)) {
            return $requested;
        }

        if (! $this->isFormatDisabled($this->defaultFormat)) {
            return $this->defaultFormat;
        }

        foreach ([ExportFormat::Xlsx, ExportFormat::Csv, ExportFormat::Pdf] as $candidate) {
            if (! $this->isFormatDisabled($candidate)) {
                return $candidate;
            }
        }

        return ExportFormat::Xlsx;
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
}
