<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Exports\CsvExporter;
use OccTherapist\AdvancedTableExportForFilament\Exports\XlsxExporter;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;
use OpenSpout\Writer\CSV\Options;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportWriterHooksTest extends TestCase
{
    public function test_modify_csv_writer_can_change_delimiter(): void
    {
        $exporter = new CsvExporter;

        $options = $this->makeOptions(
            modifyCsvWriter: function (Options $csvOptions): void {
                $csvOptions->FIELD_DELIMITER = '|';
            },
        );

        $response = $exporter->download(
            fileName: 'report',
            headers: ['name' => 'Name', 'email' => 'Email'],
            rows: [['name' => 'Ada', 'email' => 'ada@test.com']],
            options: $options,
        );

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Name|Email', $content);
        $this->assertStringContainsString('Ada|ada@test.com', $content);
    }

    public function test_modify_xlsx_writer_receives_writer_and_context(): void
    {
        $exporter = new XlsxExporter;
        $sheetName = null;

        $options = $this->makeOptions(
            modifyXlsxWriter: function (XlsxWriter $writer, array $context) use (&$sheetName): void {
                $sheetName = $writer->getCurrentSheet()->getName();
                $this->assertSame('report', $context['fileName']);
                $this->assertSame(1, $context['rowCount']);
            },
        );

        $response = $exporter->download(
            fileName: 'report',
            headers: ['name' => 'Name'],
            rows: [['name' => 'Ada']],
            options: $options,
        );

        $this->assertInstanceOf(StreamedResponse::class, $response);

        ob_start();
        $response->sendContent();
        ob_end_clean();

        $this->assertNotNull($sheetName);
    }

    protected function makeOptions(
        ?\Closure $modifyCsvWriter = null,
        ?\Closure $modifyXlsxWriter = null,
    ): TableExportOptions {
        return new TableExportOptions(
            usesSelectedRecords: false,
            additionalColumns: [],
            modifyExportQueryUsing: null,
            maxPdfRows: 200,
            maxExportRows: 2000,
            previewPerPage: 25,
            disablePdf: false,
            disableXlsx: false,
            disableCsv: false,
            defaultFormat: ExportFormat::Xlsx,
            defaultPageOrientation: 'landscape',
            disableFilterColumns: false,
            disableFileName: false,
            disableFileNamePrefix: false,
            disablePreview: false,
            disableTableColumns: false,
            includeHiddenColumns: false,
            defaultFileName: null,
            timeFormat: null,
            csvDelimiter: ',',
            formatStates: [],
            extraViewData: [],
            fileNameFieldLabel: null,
            formatFieldLabel: null,
            pageOrientationFieldLabel: null,
            filterColumnsFieldLabel: null,
            modifyPdfHtml: null,
            modifyDompdfWriter: null,
            modifyXlsxWriter: $modifyXlsxWriter,
            modifyCsvWriter: $modifyCsvWriter,
        );
    }
}
