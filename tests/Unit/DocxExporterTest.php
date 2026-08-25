<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use OccTherapist\AdvancedTableExportForFilament\Exports\DocxExporter;
use OccTherapist\AdvancedTableExportForFilament\Tests\Support\TableExportOptionsFactory;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Title;
use PhpOffice\PhpWord\PhpWord;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocxExporterTest extends TestCase
{
    public function test_it_builds_a_document_with_title_headers_and_rows(): void
    {
        $exporter = new DocxExporter;

        $phpWord = $exporter->buildDocument(
            headers: ['name' => 'Name', 'email' => 'Email'],
            rows: [['name' => 'Ada', 'email' => 'ada@example.com']],
            orientation: 'landscape',
            title: 'Users',
        );

        $sections = $phpWord->getSections();
        $this->assertCount(1, $sections);

        /** @var Section $section */
        $section = $sections[0];
        $this->assertSame('landscape', $section->getStyle()->getOrientation());

        $elements = $section->getElements();
        $this->assertInstanceOf(Title::class, $elements[0]);
        $this->assertSame('Users', $this->resolveText($elements[0]->getText()));

        $table = collect($elements)->first(fn (mixed $element): bool => $element instanceof Table);
        $this->assertInstanceOf(Table::class, $table);

        $rows = $table->getRows();
        $this->assertCount(2, $rows);
        $this->assertSame('Name', $this->cellText($rows[0]->getCells()[0]));
        $this->assertSame('Email', $this->cellText($rows[0]->getCells()[1]));
        $this->assertSame('Ada', $this->cellText($rows[1]->getCells()[0]));
        $this->assertSame('ada@example.com', $this->cellText($rows[1]->getCells()[1]));
    }

    public function test_modify_docx_document_hook_receives_document_and_context(): void
    {
        $exporter = new DocxExporter;
        $receivedFileName = null;

        $options = TableExportOptionsFactory::make([
            'modifyDocxDocument' => function (PhpWord $phpWord, array $context) use (&$receivedFileName): void {
                $receivedFileName = $context['fileName'];
                $this->assertSame('docx', $context['format']);
                $this->assertSame('portrait', $context['orientation']);
                $phpWord->addSection()->addText('hooked');
            },
        ]);

        $response = $exporter->download(
            fileName: 'report',
            headers: ['name' => 'Name'],
            rows: [['name' => 'Ada']],
            orientation: 'portrait',
            title: null,
            options: $options,
        );

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame('report', $receivedFileName);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertNotSame('', $content);
        $this->assertStringContainsString('PK', substr($content, 0, 2));
    }

    protected function cellText(mixed $cell): string
    {
        $elements = $cell->getElements();

        if ($elements === []) {
            return '';
        }

        return $this->resolveText($elements[0]);
    }

    protected function resolveText(mixed $element): string
    {
        if (is_string($element)) {
            return $element;
        }

        if ($element instanceof Text) {
            return $element->getText();
        }

        if ($element instanceof TextRun) {
            $parts = [];

            foreach ($element->getElements() as $child) {
                if ($child instanceof Text) {
                    $parts[] = $child->getText();
                }
            }

            return implode('', $parts);
        }

        return '';
    }
}
