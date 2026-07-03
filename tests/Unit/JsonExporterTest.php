<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use OccTherapist\AdvancedTableExportForFilament\Enums\JsonStructure;
use OccTherapist\AdvancedTableExportForFilament\Exports\JsonExporter;
use OccTherapist\AdvancedTableExportForFilament\Tests\Support\TableExportOptionsFactory;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JsonExporterTest extends TestCase
{
    public function test_it_exports_flat_json(): void
    {
        $exporter = new JsonExporter;
        $options = TableExportOptionsFactory::make([
            'jsonStructure' => JsonStructure::Flat,
            'prettyJson' => false,
        ]);

        $response = $exporter->download(
            fileName: 'report',
            headers: ['name' => 'Name'],
            rows: [['name' => 'Ada']],
            options: $options,
        );

        $this->assertInstanceOf(StreamedResponse::class, $response);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertSame('[{"Name":"Ada"}]', $content);
    }

    public function test_it_exports_wrapped_json(): void
    {
        $exporter = new JsonExporter;
        $options = TableExportOptionsFactory::make([
            'jsonStructure' => JsonStructure::Wrapped,
            'prettyJson' => false,
        ]);

        $payload = $exporter->buildPayload(
            headers: ['name' => 'Name'],
            rows: [['name' => 'Ada']],
            options: $options,
        );

        $this->assertSame([
            'columns' => ['Name'],
            'rows' => [['Ada']],
        ], $payload);
    }

    public function test_modify_json_export_hook_can_change_payload(): void
    {
        $exporter = new JsonExporter;
        $options = TableExportOptionsFactory::make([
            'modifyJsonExport' => fn (array $payload): array => ['data' => $payload],
        ]);

        $response = $exporter->download(
            fileName: 'report',
            headers: ['name' => 'Name'],
            rows: [['name' => 'Ada']],
            options: $options,
        );

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $decoded = json_decode($content, true);

        $this->assertSame([['Name' => 'Ada']], $decoded['data']);
    }
}
