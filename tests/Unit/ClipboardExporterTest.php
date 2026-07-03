<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use OccTherapist\AdvancedTableExportForFilament\Enums\ClipboardFormat;
use OccTherapist\AdvancedTableExportForFilament\Exports\ClipboardExporter;
use OccTherapist\AdvancedTableExportForFilament\Exports\JsonExporter;
use OccTherapist\AdvancedTableExportForFilament\Tests\Support\TableExportOptionsFactory;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;

class ClipboardExporterTest extends TestCase
{
    public function test_it_builds_tsv_content_by_default(): void
    {
        $exporter = new ClipboardExporter(new JsonExporter);
        $options = TableExportOptionsFactory::make([
            'clipboardFormat' => ClipboardFormat::Tsv,
        ]);

        $content = $exporter->buildContent(
            headers: ['name' => 'Name', 'email' => 'Email'],
            rows: [['name' => 'Ada', 'email' => 'ada@example.com']],
            options: $options,
        );

        $this->assertSame("Name\tEmail\nAda\tada@example.com", $content);
    }

    public function test_it_builds_csv_content_using_configured_delimiter(): void
    {
        $exporter = new ClipboardExporter(new JsonExporter);
        $options = TableExportOptionsFactory::make([
            'clipboardFormat' => ClipboardFormat::Csv,
            'csvDelimiter' => ';',
        ]);

        $content = $exporter->buildContent(
            headers: ['name' => 'Name'],
            rows: [['name' => 'Ada']],
            options: $options,
        );

        $this->assertSame("Name\nAda", $content);
    }

    public function test_it_builds_json_content_for_clipboard(): void
    {
        $exporter = new ClipboardExporter(new JsonExporter);
        $options = TableExportOptionsFactory::make([
            'clipboardFormat' => ClipboardFormat::Json,
            'prettyJson' => false,
        ]);

        $content = $exporter->buildContent(
            headers: ['name' => 'Name'],
            rows: [['name' => 'Ada']],
            options: $options,
        );

        $this->assertSame('[{"Name":"Ada"}]', $content);
    }
}
