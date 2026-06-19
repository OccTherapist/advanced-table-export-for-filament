<?php

namespace OccTherapist\AdvancedTableExportForFilament\Concerns;

trait InteractsWithAdvancedTableExportPlugin
{
    protected int $maxPdfRows = 200;

    protected int $maxExportRows = 2000;

    protected int $previewPerPage = 25;

    public function maxPdfRows(int $maxPdfRows): static
    {
        $this->maxPdfRows = $maxPdfRows;

        return $this;
    }

    public function getMaxPdfRows(): int
    {
        return $this->maxPdfRows;
    }

    public function maxExportRows(int $maxExportRows): static
    {
        $this->maxExportRows = $maxExportRows;

        return $this;
    }

    public function getMaxExportRows(): int
    {
        return $this->maxExportRows;
    }

    public function previewPerPage(int $previewPerPage): static
    {
        $this->previewPerPage = $previewPerPage;

        return $this;
    }

    public function getPreviewPerPage(): int
    {
        return $this->previewPerPage;
    }
}
