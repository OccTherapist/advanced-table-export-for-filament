<?php

namespace OccTherapist\AdvancedTableExportForFilament\Exports;

use Closure;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportWriterContext;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Table as TableStyle;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocxExporter
{
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
        $this->ensurePhpWordIsAvailable();

        $phpWord = $this->buildDocument($headers, $rows, $orientation, $title);
        $context = ExportWriterContext::for($fileName, $headers, $rows, ExportFormat::Docx, $orientation);

        if ($options->modifyDocxDocument instanceof Closure) {
            ($options->modifyDocxDocument)($phpWord, $context);
        }

        return response()->streamDownload(
            function () use ($phpWord): void {
                IOFactory::createWriter($phpWord, 'Word2007')->save('php://output');
            },
            $fileName.'.docx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        );
    }

    /**
     * @param  array<string, string>  $headers
     * @param  array<int, array<string, string>>  $rows
     */
    public function buildDocument(
        array $headers,
        array $rows,
        string $orientation = 'landscape',
        ?string $title = null,
    ): PhpWord {
        $this->ensurePhpWordIsAvailable();

        $phpWord = new PhpWord;
        $section = $phpWord->addSection([
            'orientation' => $orientation === 'portrait' ? 'portrait' : 'landscape',
        ]);

        if (filled($title)) {
            $section->addTitle((string) $title, 1);
            $section->addTextBreak(1);
        }

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 50,
            'unit' => TblWidth::PERCENT,
            'width' => 100 * 50,
            'layout' => TableStyle::LAYOUT_FIXED,
        ]);

        $table->addRow();

        foreach ($headers as $label) {
            $table->addCell(null, ['bgColor' => 'F3F4F6'])->addText(
                $label,
                ['bold' => true],
            );
        }

        foreach ($rows as $row) {
            $table->addRow();

            foreach (array_keys($headers) as $columnName) {
                $table->addCell()->addText((string) ($row[$columnName] ?? ''));
            }
        }

        return $phpWord;
    }

    protected function ensurePhpWordIsAvailable(): void
    {
        if (! class_exists(PhpWord::class)) {
            throw new RuntimeException(
                'phpoffice/phpword is required for DOCX exports. Run: composer require phpoffice/phpword',
            );
        }
    }
}
