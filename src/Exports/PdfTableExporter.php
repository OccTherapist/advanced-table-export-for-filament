<?php

namespace OccTherapist\AdvancedTableExportForFilament\Exports;

use OccTherapist\AdvancedTableExportForFilament\Contracts\PdfRenderer;
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
        string $orientation = 'landscape',
        ?string $title = null,
        array $extraViewData = [],
    ): StreamedResponse {
        $html = view('advanced-table-export-for-filament::pdf.table', [
            'headers' => $headers,
            'rows' => $rows,
            'title' => $title,
            'orientation' => $orientation,
            ...$extraViewData,
        ])->render();

        $base64Pdf = $this->pdfRenderer->render($html, $fileName, [
            'orientation' => $orientation,
        ]);

        return response()->streamDownload(
            callback: fn () => print (base64_decode($base64Pdf) ?: ''),
            name: $fileName.'.pdf',
            headers: ['Content-Type' => 'application/pdf'],
        );
    }
}
