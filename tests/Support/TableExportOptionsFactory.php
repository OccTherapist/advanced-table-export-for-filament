<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Support;

use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ClipboardFormat;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Enums\JsonStructure;

class TableExportOptionsFactory
{
    /**
     * @param  array<int, ExportFormat>|null  $formats
     * @param  array<string, mixed>  $overrides
     */
    public static function make(array $overrides = []): TableExportOptions
    {
        return new TableExportOptions(
            usesSelectedRecords: $overrides['usesSelectedRecords'] ?? false,
            additionalColumns: $overrides['additionalColumns'] ?? [],
            modifyExportQueryUsing: $overrides['modifyExportQueryUsing'] ?? null,
            maxPdfRows: $overrides['maxPdfRows'] ?? 200,
            maxExportRows: $overrides['maxExportRows'] ?? 2000,
            maxClipboardRows: $overrides['maxClipboardRows'] ?? 500,
            previewPerPage: $overrides['previewPerPage'] ?? 25,
            disablePdf: $overrides['disablePdf'] ?? false,
            disableXlsx: $overrides['disableXlsx'] ?? false,
            disableCsv: $overrides['disableCsv'] ?? false,
            disableDocx: $overrides['disableDocx'] ?? false,
            disableJson: $overrides['disableJson'] ?? false,
            disableXml: $overrides['disableXml'] ?? false,
            disableClipboard: $overrides['disableClipboard'] ?? false,
            formats: $overrides['formats'] ?? null,
            defaultFormat: $overrides['defaultFormat'] ?? ExportFormat::Xlsx,
            defaultPageOrientation: $overrides['defaultPageOrientation'] ?? 'landscape',
            disableFilterColumns: $overrides['disableFilterColumns'] ?? false,
            disableFileName: $overrides['disableFileName'] ?? false,
            disableFileNamePrefix: $overrides['disableFileNamePrefix'] ?? false,
            disablePreview: $overrides['disablePreview'] ?? false,
            disableTableColumns: $overrides['disableTableColumns'] ?? false,
            includeHiddenColumns: $overrides['includeHiddenColumns'] ?? false,
            defaultFileName: $overrides['defaultFileName'] ?? null,
            timeFormat: $overrides['timeFormat'] ?? null,
            csvDelimiter: $overrides['csvDelimiter'] ?? null,
            jsonStructure: $overrides['jsonStructure'] ?? JsonStructure::Flat,
            prettyJson: $overrides['prettyJson'] ?? true,
            xmlRoot: $overrides['xmlRoot'] ?? 'rows',
            xmlRowTag: $overrides['xmlRowTag'] ?? 'row',
            clipboardFormat: $overrides['clipboardFormat'] ?? ClipboardFormat::Tsv,
            formatStates: $overrides['formatStates'] ?? [],
            extraViewData: $overrides['extraViewData'] ?? [],
            fileNameFieldLabel: $overrides['fileNameFieldLabel'] ?? null,
            formatFieldLabel: $overrides['formatFieldLabel'] ?? null,
            pageOrientationFieldLabel: $overrides['pageOrientationFieldLabel'] ?? null,
            filterColumnsFieldLabel: $overrides['filterColumnsFieldLabel'] ?? null,
            groupLabel: $overrides['groupLabel'] ?? null,
            formatIcons: $overrides['formatIcons'] ?? [],
            formatLabels: $overrides['formatLabels'] ?? [],
            modifyPdfHtml: $overrides['modifyPdfHtml'] ?? null,
            modifyDompdfWriter: $overrides['modifyDompdfWriter'] ?? null,
            modifyXlsxWriter: $overrides['modifyXlsxWriter'] ?? null,
            modifyCsvWriter: $overrides['modifyCsvWriter'] ?? null,
            modifyJsonExport: $overrides['modifyJsonExport'] ?? null,
            modifyXmlExport: $overrides['modifyXmlExport'] ?? null,
            modifyDocxDocument: $overrides['modifyDocxDocument'] ?? null,
        );
    }
}
