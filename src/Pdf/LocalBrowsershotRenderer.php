<?php

namespace OccTherapist\AdvancedTableExportForFilament\Pdf;

use OccTherapist\AdvancedTableExportForFilament\Contracts\PdfRenderer;
use RuntimeException;
use Spatie\LaravelPdf\Facades\Pdf;

class LocalBrowsershotRenderer implements PdfRenderer
{
    public function render(string $html, string $filename, array $options = []): string
    {
        if (! class_exists(Pdf::class)) {
            throw new RuntimeException('spatie/laravel-pdf is required for Browsershot PDF rendering.');
        }

        $pdf = Pdf::html($html)->name($filename);

        if (($options['orientation'] ?? 'landscape') === 'landscape') {
            $pdf->landscape();
        } else {
            $pdf->portrait();
        }

        return $pdf->base64();
    }
}
