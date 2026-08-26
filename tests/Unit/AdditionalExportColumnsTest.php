<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use OccTherapist\AdvancedTableExportForFilament\Support\AdditionalExportColumns;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportColumnCollection;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportRowBuilder;
use OccTherapist\AdvancedTableExportForFilament\Tests\Support\TableFactory;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;

class AdditionalExportColumnsTest extends TestCase
{
    public function test_it_builds_columns_from_form_data(): void
    {
        $columns = AdditionalExportColumns::fromFormData([
            'Department' => 'Engineering',
            'Reviewed' => 'Yes',
        ]);

        $this->assertCount(2, $columns);
        $this->assertSame('Department', $columns[0]->getLabel());
        $this->assertSame('Reviewed', $columns[1]->getLabel());
        $this->assertNotSame($columns[0]->getName(), $columns[1]->getName());
    }

    public function test_it_skips_blank_titles(): void
    {
        $columns = AdditionalExportColumns::fromFormData([
            '' => 'ignored',
            '   ' => 'ignored',
            'Valid' => 'ok',
        ]);

        $this->assertCount(1, $columns);
        $this->assertSame('Valid', $columns->first()->getLabel());
    }

    public function test_it_returns_empty_collection_for_null_or_empty_input(): void
    {
        $this->assertTrue(AdditionalExportColumns::fromFormData(null)->isEmpty());
        $this->assertTrue(AdditionalExportColumns::fromFormData([])->isEmpty());
    }

    public function test_export_rows_include_default_values_for_additional_columns(): void
    {
        $record = new class extends Model
        {
            protected $guarded = [];
        };

        $record->forceFill(['name' => 'ada']);

        $livewire = \Mockery::mock(HasTable::class);
        $livewire->shouldReceive('getTableRecordKey')
            ->andReturnUsing(fn (Model $model): string => (string) spl_object_id($model));

        $table = TableFactory::make([
            TextColumn::make('name'),
        ]);
        $table->shouldReceive('getLivewire')->andReturn($livewire);

        $columns = ExportColumnCollection::resolve(
            table: $table,
            includeHiddenColumns: true,
        )->merge(AdditionalExportColumns::fromFormData([
            'Note' => 'Confidential',
        ]));

        $rows = ExportRowBuilder::build(
            table: $table,
            columns: $columns,
            records: collect([$record]),
        );

        $this->assertSame('ada', $rows[0]['name']);
        $noteKey = array_key_first(array_diff_key($rows[0], ['name' => true]));
        $this->assertSame('Confidential', $rows[0][$noteKey]);
    }
}
