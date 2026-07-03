<?php

namespace OccTherapist\AdvancedTableExportForFilament\Concerns;

use Filament\Tables\Table;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;

trait InteractsWithTableExportFormData
{
    /**
     * @return array<string, mixed>
     */
    protected function getDefaultExportFormData(TableExportOptions $options): array
    {
        $table = $this->getTable();

        $enabledColumns = $table instanceof Table
            ? ($options->disableFilterColumns
                ? $options->resolveDefaultEnabledColumnNames($table)
                : $options->resolvePickerColumnNames($table))
            : [];

        return [
            'format' => $options->resolveFormat($options->defaultFormat->value)->value,
            'page_orientation' => $options->defaultPageOrientation,
            'preview_page' => 1,
            'enabled_columns' => $enabledColumns,
            'file_name' => $options->defaultFileName,
        ];
    }
}
