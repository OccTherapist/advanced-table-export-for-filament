<?php

namespace OccTherapist\AdvancedTableExportForFilament\Support;

use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ExportRowBuilder
{
    /**
     * @param  Collection<int, Column>  $columns
     * @param  Collection<int, Model>  $records
     * @return array<int, array<string, string>>
     */
    public static function build(Table $table, Collection $columns, Collection $records): array
    {
        $rows = [];

        foreach ($records->values() as $index => $record) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column->getName()] = TableColumnStateResolver::sanitizeSpreadsheetCell(
                    TableColumnStateResolver::resolve($table, $column, $record, $index) ?? '',
                );
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
