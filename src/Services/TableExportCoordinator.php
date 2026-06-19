<?php

namespace OccTherapist\AdvancedTableExportForFilament\Services;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Exports\CsvExporter;
use OccTherapist\AdvancedTableExportForFilament\Exports\PdfTableExporter;
use OccTherapist\AdvancedTableExportForFilament\Exports\XlsxExporter;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportColumnCollection;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportRowBuilder;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TableExportCoordinator
{
    public function __construct(
        protected CsvExporter $csvExporter,
        protected XlsxExporter $xlsxExporter,
        protected PdfTableExporter $pdfTableExporter,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(
        Action|BulkAction $action,
        array $data,
        TableExportOptions $options,
    ): ?StreamedResponse {
        $table = $action->getTable();

        if (! $table instanceof Table) {
            Notification::make()
                ->title(__('advanced-table-export-for-filament::export.table_missing'))
                ->danger()
                ->send();

            $action->halt();

            return null;
        }

        $records = $this->resolveRecords(
            action: $action,
            options: $options,
        );

        if ($records->isEmpty()) {
            Notification::make()
                ->title(__('advanced-table-export-for-filament::export.no_records'))
                ->warning()
                ->send();

            $action->halt();

            return null;
        }

        $format = $options->resolveFormat($data['format'] ?? null);
        $limit = $format === ExportFormat::Pdf ? $options->maxPdfRows : $options->maxExportRows;

        if ($records->count() > $limit) {
            Notification::make()
                ->title(__('advanced-table-export-for-filament::export.limit_exceeded_title'))
                ->body(__('advanced-table-export-for-filament::export.limit_exceeded_body', [
                    'count' => $records->count(),
                    'limit' => $limit,
                ]))
                ->danger()
                ->send();

            $action->halt();

            return null;
        }

        $columns = $this->resolveColumns($table, $data, $options);

        if ($columns->isEmpty()) {
            Notification::make()
                ->title(__('advanced-table-export-for-filament::export.no_columns'))
                ->warning()
                ->send();

            $action->halt();

            return null;
        }

        $headerLabels = ExportColumnCollection::labels($columns);
        $rows = ExportRowBuilder::build($table, $columns, $records, $options);
        $fileName = $this->resolveFileName($data['file_name'] ?? null, $table, $options);

        try {
            return match ($format) {
                ExportFormat::Csv => $this->csvExporter->download(
                    fileName: $fileName,
                    headers: $headerLabels,
                    rows: $rows,
                    delimiter: $options->getCsvDelimiter(),
                ),
                ExportFormat::Xlsx => $this->xlsxExporter->download($fileName, $headerLabels, $rows),
                ExportFormat::Pdf => $this->pdfTableExporter->download(
                    fileName: $fileName,
                    headers: $headerLabels,
                    rows: $rows,
                    orientation: $data['page_orientation'] ?? $options->defaultPageOrientation,
                    title: $table->getHeading(),
                    extraViewData: $options->extraViewData,
                ),
            };
        } catch (RuntimeException $exception) {
            Notification::make()
                ->title(__('advanced-table-export-for-filament::export.export_failed_title'))
                ->body($exception->getMessage())
                ->danger()
                ->send();

            $action->halt();

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     headers: array<string, string>,
     *     headerKeys: array<string, string>,
     *     rows: array<int, array<string, string>>,
     *     page: int,
     *     lastPage: int,
     *     from: int,
     *     to: int,
     *     total: int,
     * }
     */
    public function preview(
        Action|BulkAction $action,
        array $data,
        TableExportOptions $options,
    ): array {
        $table = $action->getTable();

        if (! $table instanceof Table) {
            return $this->emptyPreview();
        }

        $total = $this->resolveRecordCount(
            action: $action,
            options: $options,
        );

        $page = max(1, (int) ($data['preview_page'] ?? 1));
        $lastPage = max(1, (int) ceil($total / $options->previewPerPage));

        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $records = $this->resolveRecords(
            action: $action,
            options: $options,
            page: $page,
            perPage: $options->previewPerPage,
        );

        $columns = $this->resolveColumns($table, $data, $options);
        $headerLabels = ExportColumnCollection::labels($columns);
        $rows = ExportRowBuilder::build($table, $columns, $records, $options);

        $from = $total === 0 ? 0 : (($page - 1) * $options->previewPerPage) + 1;
        $to = min($page * $options->previewPerPage, $total);

        return [
            'headers' => array_values($headerLabels),
            'headerKeys' => $headerLabels,
            'rows' => $rows,
            'page' => $page,
            'lastPage' => $lastPage,
            'from' => $from,
            'to' => $to,
            'total' => $total,
            ...$options->extraViewData,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveColumns(Table $table, array $data, TableExportOptions $options): Collection
    {
        $enabledColumns = $data['enabled_columns'] ?? null;

        if (! is_array($enabledColumns)) {
            $enabledColumns = $options->resolveDefaultEnabledColumnNames($table);
        }

        return ExportColumnCollection::resolve(
            table: $table,
            additionalColumns: $options->additionalColumns,
            enabledColumnNames: $enabledColumns,
            includeHiddenColumns: $options->includeHiddenColumns,
            disableTableColumns: $options->disableTableColumns,
        );
    }

    protected function resolveFileName(?string $fileName, Table $table, TableExportOptions $options): string
    {
        $fileName = trim((string) ($fileName ?? $options->defaultFileName ?? ''));

        if ($fileName === '') {
            $prefix = $options->disableFileNamePrefix
                ? ''
                : Str::slug((string) ($table->getHeading() ?? 'export')).'-';

            $fileName = $prefix.now()->format($options->getTimeFormat());
        }

        return Str::slug($fileName, '_');
    }

    protected function resolveRecords(
        Action|BulkAction $action,
        TableExportOptions $options,
        ?int $page = null,
        ?int $perPage = null,
    ): Collection {
        if ($options->usesSelectedRecords) {
            $records = $action->getIndividuallyAuthorizedSelectedRecords();

            if ($page !== null && $perPage !== null) {
                return $records->forPage($page, $perPage)->values();
            }

            return $records;
        }

        $query = $this->resolveQuery($action, $options);

        if ($query === null) {
            return collect();
        }

        if ($page !== null && $perPage !== null) {
            return $query->forPage($page, $perPage)->get();
        }

        return $query->get();
    }

    protected function resolveRecordCount(
        Action|BulkAction $action,
        TableExportOptions $options,
    ): int {
        if ($options->usesSelectedRecords) {
            return $action->getIndividuallyAuthorizedSelectedRecords()->count();
        }

        $query = $this->resolveQuery($action, $options);

        return $query?->count() ?? 0;
    }

    protected function resolveQuery(
        Action|BulkAction $action,
        TableExportOptions $options,
    ): ?Builder {
        $livewire = $action->getLivewire();

        if (! $livewire instanceof HasTable) {
            return null;
        }

        $query = $livewire->getTableQueryForExport();

        if ($options->modifyExportQueryUsing !== null) {
            $query = ($options->modifyExportQueryUsing)($query) ?? $query;
        }

        return $query;
    }

    /**
     * @return array{
     *     headers: array<int, string>,
     *     headerKeys: array<string, string>,
     *     rows: array<int, array<string, string>>,
     *     page: int,
     *     lastPage: int,
     *     from: int,
     *     to: int,
     *     total: int,
     * }
     */
    protected function emptyPreview(): array
    {
        return [
            'headers' => [],
            'headerKeys' => [],
            'rows' => [],
            'page' => 1,
            'lastPage' => 1,
            'from' => 0,
            'to' => 0,
            'total' => 0,
        ];
    }
}
