<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Tests\Support\TableExportOptionsFactory;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;

class TableExportOptionsTest extends TestCase
{
    public function test_it_filters_disabled_formats(): void
    {
        $options = TableExportOptionsFactory::make([
            'disablePdf' => true,
            'disableCsv' => true,
            'disableDocx' => true,
            'disableJson' => true,
            'disableXml' => true,
            'disableClipboard' => true,
        ]);

        $this->assertSame(['xlsx' => 'Excel (XLSX)'], $options->availableFormatOptions());
        $this->assertSame(ExportFormat::Xlsx, $options->resolveFormat('pdf'));
    }

    public function test_it_resolves_default_format_when_requested_format_is_disabled(): void
    {
        $options = TableExportOptionsFactory::make([
            'defaultFormat' => ExportFormat::Xlsx,
            'disablePdf' => true,
        ]);

        $this->assertSame(ExportFormat::Xlsx, $options->resolveFormat('pdf'));
    }

    public function test_it_uses_action_csv_delimiter_over_config(): void
    {
        $options = TableExportOptionsFactory::make([
            'csvDelimiter' => ';',
        ]);

        $this->assertSame(';', $options->getCsvDelimiter());
    }

    public function test_it_limits_formats_with_formats_whitelist_and_disable_flags(): void
    {
        $options = TableExportOptionsFactory::make([
            'formats' => [ExportFormat::Csv, ExportFormat::Json, ExportFormat::Pdf],
            'disableJson' => true,
        ]);

        $this->assertSame(
            [ExportFormat::Csv, ExportFormat::Pdf],
            $options->availableFormats(),
        );
    }

    public function test_it_resolves_row_limits_per_format(): void
    {
        $options = TableExportOptionsFactory::make([
            'maxPdfRows' => 100,
            'maxExportRows' => 1000,
            'maxClipboardRows' => 250,
        ]);

        $this->assertSame(100, $options->resolveRowLimit(ExportFormat::Pdf));
        $this->assertSame(1000, $options->resolveRowLimit(ExportFormat::Json));
        $this->assertSame(250, $options->resolveRowLimit(ExportFormat::Clipboard));
    }

    protected function makeOptions(
        ExportFormat $defaultFormat = ExportFormat::Xlsx,
        bool $disablePdf = false,
        bool $disableCsv = false,
        ?string $csvDelimiter = null,
    ): TableExportOptions {
        return TableExportOptionsFactory::make([
            'defaultFormat' => $defaultFormat,
            'disablePdf' => $disablePdf,
            'disableCsv' => $disableCsv,
            'csvDelimiter' => $csvDelimiter,
        ]);
    }
}
