<?php

namespace OccTherapist\AdvancedTableExportForFilament\Exports;

use Closure;
use OccTherapist\AdvancedTableExportForFilament\Contracts\PdfRenderer;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportWriterContext;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfTableExporter
{
    public function __construct(
        protected PdfRenderer $pdfRenderer,
    ) {}

    /**
     * @param  array<string, string>  $headers
     * @param  array<int, array<string, string>>  $rows
     */
    public function download(
        string $fileName,
        array $headers,
        array $rows,
        string $orientation,
        ?string $title,
        TableExportOptions $options,
    ): StreamedResponse {
        $context = ExportWriterContext::for($fileName, $headers, $rows, ExportFormat::Pdf, $orientation);

        $html = view('advanced-table-export-for-filament::pdf.table', [
            'headers' => $headers,
            'rows' => $rows,
            'title' => $title,
            'orientation' => $orientation,
            ...$options->extraViewData,
        ])->render();

        if ($options->modifyPdfHtml instanceof Closure) {
            $html = (string) ($options->modifyPdfHtml)($html, $context);
        }

        $renderOptions = [
            'orientation' => $orientation,
            'context' => $context,
        ];

        if ($options->modifyDompdfWriter instanceof Closure) {
            $renderOptions['modifyDompdfWriter'] = $options->modifyDompdfWriter;
        }

        $base64Pdf = $this->pdfRenderer->render($html, $fileName, $renderOptions);

        return response()->streamDownload(
            callback: fn () => print (base64_decode($base64Pdf) ?: ''),
            name: $fileName.'.pdf',
            headers: ['Content-Type' => 'application/pdf'],
        );
    }
}
