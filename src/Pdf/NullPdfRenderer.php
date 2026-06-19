<?php

namespace OccTherapist\AdvancedTableExportForFilament\Pdf;

use OccTherapist\AdvancedTableExportForFilament\Contracts\PdfRenderer;
use RuntimeException;

class NullPdfRenderer implements PdfRenderer
{
    public function render(string $html, string $filename, array $options = []): string
    {
        throw new RuntimeException('PDF rendering is not configured. Set ADVANCED_TABLE_EXPORT_PDF_RENDERER or bind a PdfRenderer implementation.');
    }
}
