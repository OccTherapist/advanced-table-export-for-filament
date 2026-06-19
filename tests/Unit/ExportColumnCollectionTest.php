<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use Filament\Tables\Columns\TextColumn;
use OccTherapist\AdvancedTableExportForFilament\Support\ExportColumnCollection;
use OccTherapist\AdvancedTableExportForFilament\Tests\Support\TableFactory;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;

class ExportColumnCollectionTest extends TestCase
{
    public function test_it_filters_columns_by_enabled_names(): void
    {
        $table = TableFactory::make([
            TextColumn::make('name')->label('Name'),
            TextColumn::make('email')->label('Email'),
        ]);

        $columns = ExportColumnCollection::resolve(
            table: $table,
            enabledColumnNames: ['email'],
        );

        $this->assertCount(1, $columns);
        $this->assertSame('email', $columns->first()->getName());
    }

    public function test_it_merges_additional_columns(): void
    {
        $table = TableFactory::make([
            TextColumn::make('name')->label('Name'),
        ]);

        $columns = ExportColumnCollection::resolve(
            table: $table,
            additionalColumns: [
                TextColumn::make('internal_id')->label('Internal ID'),
            ],
            includeHiddenColumns: true,
        );

        $this->assertCount(2, $columns);
        $this->assertSame(['name', 'internal_id'], $columns->map->getName()->all());
    }

    public function test_it_builds_header_labels(): void
    {
        $table = TableFactory::make([
            TextColumn::make('name')->label('Full Name'),
        ]);

        $columns = ExportColumnCollection::resolve(table: $table, includeHiddenColumns: true);

        $this->assertSame(['name' => 'Full Name'], ExportColumnCollection::labels($columns));
    }
}
