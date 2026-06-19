<?php

namespace OccTherapist\AdvancedTableExportForFilament\Exports;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class XlsxExporter
{
    /**
     * @param  array<string, string>  $headers
     * @param  array<int, array<string, string>>  $rows
     */
    public function download(string $fileName, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $writer = new XlsxWriter;
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
        }, $fileName.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
