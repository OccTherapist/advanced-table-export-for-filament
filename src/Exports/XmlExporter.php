<?php

namespace OccTherapist\AdvancedTableExportForFilament\Exports;

use Closure;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportWriterContext;
use OccTherapist\AdvancedTableExportForFilament\Support\XmlColumnTagResolver;
use Symfony\Component\HttpFoundation\StreamedResponse;

class XmlExporter
{
    /**
     * @param  array<string, string>  $headers
     * @param  array<int, array<string, string>>  $rows
     */
    public function download(
        string $fileName,
        array $headers,
        array $rows,
        TableExportOptions $options,
    ): StreamedResponse {
        $content = $this->buildXml($headers, $rows, $options);
        $context = ExportWriterContext::for($fileName, $headers, $rows, ExportFormat::Xml);

        if ($options->modifyXmlExport instanceof Closure) {
            $content = ($options->modifyXmlExport)($content, $context) ?? $content;
        }

        return response()->streamDownload(
            function () use ($content): void {
                echo $content;
            },
            $fileName.'.xml',
            ['Content-Type' => 'application/xml; charset=UTF-8'],
        );
    }

    /**
     * @param  array<string, string>  $headers
     * @param  array<int, array<string, string>>  $rows
     */
    public function buildXml(array $headers, array $rows, TableExportOptions $options): string
    {
        $root = $this->sanitizeRootTag($options->xmlRoot, 'rows');
        $rowTag = $this->sanitizeRootTag($options->xmlRowTag, 'row');
        $resolver = new XmlColumnTagResolver;

        $xml = new \XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement($root);

        foreach ($rows as $row) {
            $xml->startElement($rowTag);

            foreach ($headers as $columnName => $label) {
                $tag = $resolver->resolve($columnName, $label);
                $value = $row[$columnName] ?? '';

                if ($tag['attribute'] !== null) {
                    $xml->startElement($tag['element']);
                    $xml->writeAttribute('name', $tag['attribute']);
                    $xml->text($value);
                    $xml->endElement();

                    continue;
                }

                $xml->writeElement($tag['element'], $value);
            }

            $xml->endElement();
        }

        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    protected function sanitizeRootTag(string $value, string $fallback): string
    {
        $tag = preg_replace('/[^a-zA-Z0-9_-]/', '_', $value) ?? '';
        $tag = trim($tag, '_');

        if ($tag === '') {
            return $fallback;
        }

        if (preg_match('/^[0-9]/', $tag)) {
            $tag = '_'.$tag;
        }

        return $tag;
    }
}
