<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Exports\CsvExporter;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporterTest extends TestCase
{
    public function test_it_streams_csv_with_custom_delimiter(): void
    {
        $exporter = new CsvExporter;

        $options = new TableExportOptions(
            usesSelectedRecords: false,
            additionalColumns: [],
            modifyExportQueryUsing: null,
            maxPdfRows: 200,
            maxExportRows: 2000,
            previewPerPage: 25,
            disablePdf: false,
            disableXlsx: false,
            disableCsv: false,
            defaultFormat: ExportFormat::Csv,
            defaultPageOrientation: 'landscape',
            disableFilterColumns: false,
            disableFileName: false,
            disableFileNamePrefix: false,
            disablePreview: false,
            disableTableColumns: false,
            includeHiddenColumns: false,
            defaultFileName: null,
            timeFormat: null,
            csvDelimiter: ';',
            formatStates: [],
            extraViewData: [],
            fileNameFieldLabel: null,
            formatFieldLabel: null,
            pageOrientationFieldLabel: null,
            filterColumnsFieldLabel: null,
        );

        $response = $exporter->download(
            fileName: 'report',
            headers: ['name' => 'Name', 'email' => 'Email'],
            rows: [
                ['name' => 'Ada', 'email' => 'ada@example.com'],
            ],
            options: $options,
        );

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString('report.csv', (string) $response->headers->get('Content-Disposition'));

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Name;Email', $content);
        $this->assertStringContainsString('Ada;ada@example.com', $content);
    }
}
