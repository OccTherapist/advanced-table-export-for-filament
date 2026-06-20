<?php

namespace OccTherapist\AdvancedTableExportForFilament\Exports;

use Closure;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportWriterContext;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Options;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporter
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
        $context = ExportWriterContext::for($fileName, $headers, $rows, ExportFormat::Csv);

        return response()->streamDownload(function () use ($headers, $rows, $options, $context): void {
            $csvOptions = new Options;
            $csvOptions->FIELD_DELIMITER = $options->getCsvDelimiter();

            if ($options->modifyCsvWriter instanceof Closure) {
                ($options->modifyCsvWriter)($csvOptions, $context);
            }

            $writer = new CsvWriter($csvOptions);
            $writer->openToFile('php://output');

            $writer->addRow(Row::fromValues(array_values($headers)));

            foreach ($rows as $row) {
                $ordered = [];

                foreach (array_keys($headers) as $key) {
                    $ordered[] = $row[$key] ?? '';
                }

                $writer->addRow(Row::fromValues($ordered));
            }

            $writer->close();
        }, $fileName.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
