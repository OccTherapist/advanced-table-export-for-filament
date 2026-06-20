<?php

namespace OccTherapist\AdvancedTableExportForFilament\Pdf;

use Closure;
use Dompdf\Dompdf;
use OccTherapist\AdvancedTableExportForFilament\Contracts\PdfRenderer;
use RuntimeException;

class DompdfRenderer implements PdfRenderer
{
    public function render(string $html, string $filename, array $options = []): string
    {
        if (! class_exists(Dompdf::class)) {
            throw new RuntimeException('dompdf/dompdf is required for Dompdf PDF rendering.');
        }

        $dompdf = new Dompdf;
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', ($options['orientation'] ?? 'landscape') === 'landscape' ? 'landscape' : 'portrait');

        if (isset($options['modifyDompdfWriter']) && $options['modifyDompdfWriter'] instanceof Closure) {
            ($options['modifyDompdfWriter'])($dompdf, $options['context'] ?? []);
        }

        $dompdf->render();

        return base64_encode($dompdf->output());
    }
}
