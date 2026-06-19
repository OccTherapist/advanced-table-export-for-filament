<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use OccTherapist\AdvancedTableExportForFilament\Exports\CsvExporter;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExporterTest extends TestCase
{
    public function test_it_streams_csv_with_custom_delimiter(): void
    {
        $exporter = new CsvExporter;

        $response = $exporter->download(
            fileName: 'report',
            headers: ['name' => 'Name', 'email' => 'Email'],
            rows: [
                ['name' => 'Ada', 'email' => 'ada@example.com'],
            ],
            delimiter: ';',
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
