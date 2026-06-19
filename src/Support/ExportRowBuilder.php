<?php

namespace OccTherapist\AdvancedTableExportForFilament\Support;

use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;

class ExportRowBuilder
{
    /**
     * @param  Collection<int, Column>  $columns
     * @param  Collection<int, Model>  $records
     * @return array<int, array<string, string>>
     */
    public static function build(
        Table $table,
        Collection $columns,
        Collection $records,
        ?TableExportOptions $options = null,
    ): array {
        $rows = [];

        foreach ($records->values() as $index => $record) {
            $row = [];

            foreach ($columns as $column) {
                if ($options !== null && isset($options->formatStates[$column->getName()])) {
                    $formatted = $options->formatStates[$column->getName()]($record);
                    $value = $formatted === null ? '' : (string) $formatted;
                } else {
                    $value = TableColumnStateResolver::resolve($table, $column, $record, $index) ?? '';
                }

                $row[$column->getName()] = TableColumnStateResolver::sanitizeSpreadsheetCell($value);
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
