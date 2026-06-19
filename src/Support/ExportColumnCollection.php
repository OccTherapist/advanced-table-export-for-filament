<?php

namespace OccTherapist\AdvancedTableExportForFilament\Support;

use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class ExportColumnCollection
{
    /**
     * @param  array<int, Column>  $additionalColumns
     */
    public static function resolve(
        Table $table,
        array $additionalColumns = [],
        ?array $enabledColumnNames = null,
        bool $includeHiddenColumns = false,
    ): Collection {
        $useAllColumns = $includeHiddenColumns || $enabledColumnNames !== null;

        $columns = collect($useAllColumns ? $table->getColumns() : $table->getVisibleColumns());

        if ($additionalColumns !== []) {
            $columns = $columns->merge($additionalColumns);
        }

        if ($enabledColumnNames !== null) {
            $columns = $columns->filter(
                fn (Column $column): bool => in_array($column->getName(), $enabledColumnNames, true),
            );
        }

        return $columns->values();
    }

    /**
     * @param  Collection<int, Column>  $columns
     * @return array<string, string>
     */
    public static function labels(Collection $columns): array
    {
        return $columns
            ->mapWithKeys(function (Column $column): array {
                $label = $column->getLabel();

                if ($label instanceof HtmlString) {
                    $label = strip_tags($label->toHtml());
                }

                return [$column->getName() => (string) $label];
            })
            ->all();
    }
}
