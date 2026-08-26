<?php

namespace OccTherapist\AdvancedTableExportForFilament\Support;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdditionalExportColumns
{
    /**
     * Convert end-user KeyValue form data (title => default value) into export columns.
     *
     * @param  array<string, mixed>|null  $additionalColumns
     * @return Collection<int, TextColumn>
     */
    public static function fromFormData(?array $additionalColumns): Collection
    {
        if ($additionalColumns === null || $additionalColumns === []) {
            return collect();
        }

        return collect($additionalColumns)
            ->filter(fn (mixed $value, mixed $title): bool => is_string($title) && trim($title) !== '')
            ->map(function (mixed $value, string $title): TextColumn {
                $uniqueName = Str::snake($title).'_'.Str::lower(Str::random(8));

                return TextColumn::make($uniqueName)
                    ->label($title)
                    ->state($value === null ? '' : (string) $value);
            })
            ->values();
    }
}
