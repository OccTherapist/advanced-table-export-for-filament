<?php

namespace OccTherapist\AdvancedTableExportForFilament\Services;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
     * @param  array<int, mixed>  $additionalColumns
     */
    public function handle(
        Action|BulkAction $action,
        array $data,
        bool $usesSelectedRecords,
        array $additionalColumns = [],
        ?Closure $modifyExportQueryUsing = null,
        int $maxPdfRows = 200,
        int $maxExportRows = 2000,
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
            usesSelectedRecords: $usesSelectedRecords,
            modifyExportQueryUsing: $modifyExportQueryUsing,
        );

        if ($records->isEmpty()) {
            Notification::make()
                ->title(__('advanced-table-export-for-filament::export.no_records'))
                ->warning()
                ->send();

            $action->halt();

            return null;
        }

        $format = $data['format'] ?? ExportFormat::Xlsx->value;
        $format = $format instanceof ExportFormat ? $format : ExportFormat::from((string) $format);
        $limit = $format === ExportFormat::Pdf ? $maxPdfRows : $maxExportRows;

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

        $enabledColumns = $data['enabled_columns'] ?? null;
        $columns = ExportColumnCollection::resolve(
            table: $table,
            additionalColumns: $additionalColumns,
            enabledColumnNames: is_array($enabledColumns) ? $enabledColumns : null,
        );

        if ($columns->isEmpty()) {
            Notification::make()
                ->title(__('advanced-table-export-for-filament::export.no_columns'))
                ->warning()
                ->send();

            $action->halt();

            return null;
        }

        $headerLabels = ExportColumnCollection::labels($columns);
        $rows = ExportRowBuilder::build($table, $columns, $records);
        $fileName = $this->resolveFileName($data['file_name'] ?? null, $table);

        try {
            return match ($format) {
                ExportFormat::Csv => $this->csvExporter->download(
                    fileName: $fileName,
                    headers: $headerLabels,
                    rows: $rows,
                    delimiter: (string) config('advanced-table-export-for-filament.csv_delimiter', ','),
                ),
                ExportFormat::Xlsx => $this->xlsxExporter->download($fileName, $headerLabels, $rows),
                ExportFormat::Pdf => $this->pdfTableExporter->download(
                    fileName: $fileName,
                    headers: $headerLabels,
                    rows: $rows,
                    orientation: $data['page_orientation'] ?? config('advanced-table-export-for-filament.default_page_orientation', 'landscape'),
                    title: $table->getHeading(),
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
        bool $usesSelectedRecords,
        array $additionalColumns = [],
        ?Closure $modifyExportQueryUsing = null,
        int $previewPerPage = 25,
    ): array {
        $table = $action->getTable();

        if (! $table instanceof Table) {
            return $this->emptyPreview();
        }

        $total = $this->resolveRecordCount(
            action: $action,
            usesSelectedRecords: $usesSelectedRecords,
            modifyExportQueryUsing: $modifyExportQueryUsing,
        );

        $page = max(1, (int) ($data['preview_page'] ?? 1));
        $lastPage = max(1, (int) ceil($total / $previewPerPage));

        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $records = $this->resolveRecords(
            action: $action,
            usesSelectedRecords: $usesSelectedRecords,
            modifyExportQueryUsing: $modifyExportQueryUsing,
            page: $page,
            perPage: $previewPerPage,
        );

        $enabledColumns = $data['enabled_columns'] ?? null;
        $columns = ExportColumnCollection::resolve(
            table: $table,
            additionalColumns: $additionalColumns,
            enabledColumnNames: is_array($enabledColumns) ? $enabledColumns : null,
        );

        $headerLabels = ExportColumnCollection::labels($columns);
        $rows = ExportRowBuilder::build($table, $columns, $records);

        $from = $total === 0 ? 0 : (($page - 1) * $previewPerPage) + 1;
        $to = min($page * $previewPerPage, $total);

        return [
            'headers' => array_values($headerLabels),
            'headerKeys' => $headerLabels,
            'rows' => $rows,
            'page' => $page,
            'lastPage' => $lastPage,
            'from' => $from,
            'to' => $to,
            'total' => $total,
        ];
    }

    protected function resolveFileName(?string $fileName, Table $table): string
    {
        $fileName = trim((string) $fileName);

        if ($fileName === '') {
            $prefix = config('advanced-table-export-for-filament.disable_file_name_prefix')
                ? ''
                : Str::slug((string) ($table->getHeading() ?? 'export')).'-';

            $fileName = $prefix.now()->format(config('advanced-table-export-for-filament.time_format', 'Y-m-d_H-i'));
        }

        return Str::slug($fileName, '_');
    }

    protected function resolveRecords(
        Action|BulkAction $action,
        bool $usesSelectedRecords,
        ?Closure $modifyExportQueryUsing = null,
        ?int $page = null,
        ?int $perPage = null,
    ): Collection {
        if ($usesSelectedRecords) {
            $records = $action->getIndividuallyAuthorizedSelectedRecords();

            if ($page !== null && $perPage !== null) {
                return $records->forPage($page, $perPage)->values();
            }

            return $records;
        }

        $query = $this->resolveQuery($action, $modifyExportQueryUsing);

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
        bool $usesSelectedRecords,
        ?Closure $modifyExportQueryUsing = null,
    ): int {
        if ($usesSelectedRecords) {
            return $action->getIndividuallyAuthorizedSelectedRecords()->count();
        }

        $query = $this->resolveQuery($action, $modifyExportQueryUsing);

        return $query?->count() ?? 0;
    }

    protected function resolveQuery(
        Action|BulkAction $action,
        ?Closure $modifyExportQueryUsing = null,
    ): ?Builder {
        $livewire = $action->getLivewire();

        if (! $livewire instanceof HasTable) {
            return null;
        }

        $query = $livewire->getTableQueryForExport();

        if ($modifyExportQueryUsing !== null) {
            $query = $modifyExportQueryUsing($query) ?? $query;
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
