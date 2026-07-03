<?php

namespace OccTherapist\AdvancedTableExportForFilament\Exports;

use Closure;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Enums\JsonStructure;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportWriterContext;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JsonExporter
{
    /**
     * @param  array<string, string>  $headers
     * @param  array<int, array<string, string>>  $rows
     */
    public function download(
        string $fileName,
        array $headers,
        array $rows,
        TableExportOptions $options,
    ): StreamedResponse {
        $payload = $this->buildPayload($headers, $rows, $options);
        $context = ExportWriterContext::for($fileName, $headers, $rows, ExportFormat::Json);

        if ($options->modifyJsonExport instanceof Closure) {
            $payload = ($options->modifyJsonExport)($payload, $context) ?? $payload;
        }

        $flags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE;

        if ($options->prettyJson) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $content = json_encode($payload, $flags);

        return response()->streamDownload(
            function () use ($content): void {
                echo $content;
            },
            $fileName.'.json',
            ['Content-Type' => 'application/json; charset=UTF-8'],
        );
    }

    /**
     * @param  array<string, string>  $headers
     * @param  array<int, array<string, string>>  $rows
     * @return array<mixed>
     */
    public function buildPayload(array $headers, array $rows, TableExportOptions $options): array
    {
        $mappedRows = array_map(
            fn (array $row): array => $this->mapRow($headers, $row),
            $rows,
        );

        if ($options->jsonStructure === JsonStructure::Wrapped) {
            return [
                'columns' => array_values($headers),
                'rows' => array_map(
                    fn (array $row): array => array_values($row),
                    $mappedRows,
                ),
            ];
        }

        return $mappedRows;
    }

    /**
     * @param  array<string, string>  $headers
     * @param  array<string, string>  $row
     * @return array<string, string>
     */
    protected function mapRow(array $headers, array $row): array
    {
        $mapped = [];

        foreach ($headers as $columnName => $label) {
            $mapped[$label] = $row[$columnName] ?? '';
        }

        return $mapped;
    }
}
