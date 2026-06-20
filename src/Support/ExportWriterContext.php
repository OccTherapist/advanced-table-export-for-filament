<?php

namespace OccTherapist\AdvancedTableExportForFilament\Support;

use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;

class ExportWriterContext
{
    /**
     * @param  array<string, string>  $headers
     * @param  array<int, array<string, string>>  $rows
     * @return array{
     *     fileName: string,
     *     headers: array<string, string>,
     *     rows: array<int, array<string, string>>,
     *     rowCount: int,
     *     format: string,
     *     orientation: string|null,
     * }
     */
    public static function for(
        string $fileName,
        array $headers,
        array $rows,
        ExportFormat $format,
        ?string $orientation = null,
    ): array {
        return [
            'fileName' => $fileName,
            'headers' => $headers,
            'rows' => $rows,
            'rowCount' => count($rows),
            'format' => $format->value,
            'orientation' => $orientation,
        ];
    }
}
