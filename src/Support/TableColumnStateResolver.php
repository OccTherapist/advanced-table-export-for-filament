<?php

namespace OccTherapist\AdvancedTableExportForFilament\Support;

use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Concerns\CanFormatState;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Stringable;

class TableColumnStateResolver
{
    public static function resolve(Table $table, Column $column, Model $record, int $index): ?string
    {
        $column->rowLoop((object) [
            'index' => $index,
            'iteration' => $index + 1,
        ]);

        $column->record($record);
        $column->table($table);

        $state = $column->getState();

        if (in_array(CanFormatState::class, class_uses_recursive($column), true)) {
            $state = $column->formatState($state);
        }

        return self::stringifyState($column, $state);
    }

    protected static function stringifyState(Column $column, mixed $state): ?string
    {
        if ($state instanceof HtmlString) {
            return trim(preg_replace('/\s+/', ' ', strip_tags($state->toHtml())) ?? '');
        }

        if ($state instanceof Stringable) {
            return (string) $state;
        }

        if (is_array($state)) {
            return implode(', ', array_map(
                fn (mixed $value): string => is_scalar($value) || $value === null ? (string) $value : json_encode($value),
                $state,
            ));
        }

        if ($column instanceof ImageColumn) {
            return $column->getImageUrl();
        }

        if ($column instanceof ViewColumn) {
            return trim(preg_replace('/\s+/', ' ', strip_tags($column->render()->render())) ?? '');
        }

        if (is_bool($state)) {
            return $state ? '1' : '0';
        }

        return $state === null ? '' : (string) $state;
    }

    public static function sanitizeSpreadsheetCell(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        if (preg_match('/^[=+\-@]/', $value)) {
            return "'".$value;
        }

        return $value;
    }
}
