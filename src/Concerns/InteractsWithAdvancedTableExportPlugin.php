<?php

namespace OccTherapist\AdvancedTableExportForFilament\Concerns;

use OccTherapist\AdvancedTableExportForFilament\Enums\ClipboardFormat;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Enums\JsonStructure;

trait InteractsWithAdvancedTableExportPlugin
{
    protected int $maxPdfRows = 200;

    protected int $maxExportRows = 2000;

    protected int $maxClipboardRows = 500;

    protected int $previewPerPage = 25;

    /** @var array<int, ExportFormat>|null */
    protected ?array $formats = null;

    protected ?ClipboardFormat $clipboardFormat = null;

    protected ?JsonStructure $jsonStructure = null;

    protected ?bool $prettyJson = null;

    protected ?string $xmlRoot = null;

    protected ?string $xmlRowTag = null;

    /**
     * @param  array<int, ExportFormat>  $formats
     */
    public function formats(array $formats): static
    {
        $this->formats = $formats;

        return $this;
    }

    /**
     * @return array<int, ExportFormat>|null
     */
    public function getFormats(): ?array
    {
        return $this->formats;
    }

    public function clipboardFormat(ClipboardFormat $format): static
    {
        $this->clipboardFormat = $format;

        return $this;
    }

    public function getClipboardFormat(): ?ClipboardFormat
    {
        return $this->clipboardFormat;
    }

    public function jsonStructure(JsonStructure $structure): static
    {
        $this->jsonStructure = $structure;

        return $this;
    }

    public function getJsonStructure(): ?JsonStructure
    {
        return $this->jsonStructure;
    }

    public function prettyJson(bool $condition = true): static
    {
        $this->prettyJson = $condition;

        return $this;
    }

    public function getPrettyJson(): ?bool
    {
        return $this->prettyJson;
    }

    public function xmlRoot(string $root): static
    {
        $this->xmlRoot = $root;

        return $this;
    }

    public function getXmlRoot(): ?string
    {
        return $this->xmlRoot;
    }

    public function xmlRowTag(string $rowTag): static
    {
        $this->xmlRowTag = $rowTag;

        return $this;
    }

    public function getXmlRowTag(): ?string
    {
        return $this->xmlRowTag;
    }

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

    public function maxClipboardRows(int $maxClipboardRows): static
    {
        $this->maxClipboardRows = $maxClipboardRows;

        return $this;
    }

    public function getMaxClipboardRows(): int
    {
        return $this->maxClipboardRows;
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
