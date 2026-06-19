<?php

namespace OccTherapist\AdvancedTableExportForFilament\Exports;

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
    public function download(string $fileName, array $headers, array $rows, string $delimiter = ','): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows, $delimiter): void {
            $options = new Options;
            $options->FIELD_DELIMITER = $delimiter;

            $writer = new CsvWriter($options);
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
