<?php

namespace OccTherapist\AdvancedTableExportForFilament\Exports;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ClipboardFormat;

class ClipboardExporter
{
    public function __construct(
        protected JsonExporter $jsonExporter,
    ) {}

    /**
     * @param  array<string, string>  $headers
     * @param  array<int, array<string, string>>  $rows
     */
    public function copy(
        Action|BulkAction $action,
        array $headers,
        array $rows,
        TableExportOptions $options,
    ): void {
        $content = $this->buildContent($headers, $rows, $options);
        $encoded = json_encode($content, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);

        $livewire = $action->getLivewire();

        if (method_exists($livewire, 'js')) {
            $livewire->js('navigator.clipboard.writeText('.$encoded.')');
        } else {
            $livewire->dispatch('advanced-table-export-copy-to-clipboard', content: $content);
        }

        Notification::make()
            ->title(__('advanced-table-export-for-filament::export.copied_to_clipboard'))
            ->success()
            ->send();
    }

    /**
     * @param  array<string, string>  $headers
     * @param  array<int, array<string, string>>  $rows
     */
    public function buildContent(array $headers, array $rows, TableExportOptions $options): string
    {
        return match ($options->clipboardFormat) {
            ClipboardFormat::Csv => $this->buildDelimitedContent($headers, $rows, $options->getCsvDelimiter()),
            ClipboardFormat::Json => json_encode(
                $this->jsonExporter->buildPayload($headers, $rows, $options),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | ($options->prettyJson ? JSON_PRETTY_PRINT : 0),
            ),
            ClipboardFormat::Tsv => $this->buildDelimitedContent($headers, $rows, "\t"),
        };
    }

    /**
     * @param  array<string, string>  $headers
     * @param  array<int, array<string, string>>  $rows
     */
    protected function buildDelimitedContent(array $headers, array $rows, string $delimiter): string
    {
        $lines = [
            implode($delimiter, array_map(
                fn (string $value): string => $this->escapeCell($value, $delimiter),
                array_values($headers),
            )),
        ];

        foreach ($rows as $row) {
            $cells = [];

            foreach (array_keys($headers) as $columnName) {
                $cells[] = $this->escapeCell($row[$columnName] ?? '', $delimiter);
            }

            $lines[] = implode($delimiter, $cells);
        }

        return implode("\n", $lines);
    }

    protected function escapeCell(string $value, string $delimiter): string
    {
        if (str_contains($value, $delimiter) || str_contains($value, '"') || str_contains($value, "\n") || str_contains($value, "\r")) {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }
}
