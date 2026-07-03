<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use OccTherapist\AdvancedTableExportForFilament\Exports\XmlExporter;
use OccTherapist\AdvancedTableExportForFilament\Support\XmlColumnTagResolver;
use OccTherapist\AdvancedTableExportForFilament\Tests\Support\TableExportOptionsFactory;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;

class XmlExporterTest extends TestCase
{
    public function test_it_exports_rows_with_default_tags(): void
    {
        $exporter = new XmlExporter;
        $options = TableExportOptionsFactory::make();

        $xml = $exporter->buildXml(
            headers: ['name' => 'Name', 'email' => 'Email'],
            rows: [['name' => 'Ada', 'email' => 'ada@example.com']],
            options: $options,
        );

        $this->assertStringContainsString('<rows>', $xml);
        $this->assertStringContainsString('<row>', $xml);
        $this->assertStringContainsString('<name>Ada</name>', $xml);
        $this->assertStringContainsString('<email>ada@example.com</email>', $xml);
    }

    public function test_it_falls_back_to_field_elements_for_invalid_column_names(): void
    {
        $resolver = new XmlColumnTagResolver;

        $resolver->resolve('user.email', 'User Email');
        $tag = $resolver->resolve('user.email', 'Duplicate');

        $this->assertSame('field', $tag['element']);
        $this->assertSame('user.email', $tag['attribute']);
    }

    public function test_modify_xml_export_hook_can_change_output(): void
    {
        $exporter = new XmlExporter;
        $options = TableExportOptionsFactory::make([
            'modifyXmlExport' => fn (string $xml): string => '<custom>'.$xml.'</custom>',
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

        $this->assertStringStartsWith('<custom>', $content);
        $this->assertStringEndsWith('</custom>', $content);
    }
}
