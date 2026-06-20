<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;

class TableExportOptionsTest extends TestCase
{
    public function test_it_filters_disabled_formats(): void
    {
        $options = $this->makeOptions(disablePdf: true, disableCsv: true);

        $this->assertSame(['xlsx' => 'XLSX'], $options->availableFormatOptions());
        $this->assertSame(ExportFormat::Xlsx, $options->resolveFormat('pdf'));
    }

    public function test_it_resolves_default_format_when_requested_format_is_disabled(): void
    {
        $options = $this->makeOptions(
            defaultFormat: ExportFormat::Xlsx,
            disablePdf: true,
        );

        $this->assertSame(ExportFormat::Xlsx, $options->resolveFormat('pdf'));
    }

    public function test_it_uses_action_csv_delimiter_over_config(): void
    {
        $options = $this->makeOptions(csvDelimiter: ';');

        $this->assertSame(';', $options->getCsvDelimiter());
    }

    protected function makeOptions(
        ExportFormat $defaultFormat = ExportFormat::Xlsx,
        bool $disablePdf = false,
        bool $disableCsv = false,
        ?string $csvDelimiter = null,
    ): TableExportOptions {
        return new TableExportOptions(
            usesSelectedRecords: false,
            additionalColumns: [],
            modifyExportQueryUsing: null,
            maxPdfRows: 200,
            maxExportRows: 2000,
            previewPerPage: 25,
            disablePdf: $disablePdf,
            disableXlsx: false,
            disableCsv: $disableCsv,
            defaultFormat: $defaultFormat,
            defaultPageOrientation: 'landscape',
            disableFilterColumns: false,
            disableFileName: false,
            disableFileNamePrefix: false,
            disablePreview: false,
            disableTableColumns: false,
            includeHiddenColumns: false,
            defaultFileName: null,
            timeFormat: null,
            csvDelimiter: $csvDelimiter,
            formatStates: [],
            extraViewData: [],
            fileNameFieldLabel: null,
            formatFieldLabel: null,
            pageOrientationFieldLabel: null,
            filterColumnsFieldLabel: null,
            modifyPdfHtml: null,
            modifyDompdfWriter: null,
            modifyXlsxWriter: null,
            modifyCsvWriter: null,
        );
    }
}
