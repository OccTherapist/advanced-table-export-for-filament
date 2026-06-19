<?php

namespace OccTherapist\AdvancedTableExportForFilament\Contracts;

interface PdfRenderer
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function render(string $html, string $filename, array $options = []): string;
}
