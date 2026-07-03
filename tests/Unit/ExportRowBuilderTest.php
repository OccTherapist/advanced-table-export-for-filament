<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportColumnCollection;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportRowBuilder;
use OccTherapist\AdvancedTableExportForFilament\Tests\Support\TableExportOptionsFactory;
use OccTherapist\AdvancedTableExportForFilament\Tests\Support\TableFactory;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;

class ExportRowBuilderTest extends TestCase
{
    public function test_it_applies_format_states(): void
    {
        $record = new class extends Model
        {
            protected $guarded = [];
        };

        $record->forceFill(['name' => 'ada']);

        $table = TableFactory::make([
            TextColumn::make('name'),
        ]);

        $columns = ExportColumnCollection::resolve(
            table: $table,
            includeHiddenColumns: true,
        );

        $options = TableExportOptionsFactory::make([
            'formatStates' => [
                'name' => fn (Model $record): string => strtoupper((string) $record->name),
            ],
        ]);

        $rows = ExportRowBuilder::build(
            table: $table,
            columns: $columns,
            records: collect([$record]),
            options: $options,
        );

        $this->assertSame(['name' => 'ADA'], $rows[0]);
    }
}
