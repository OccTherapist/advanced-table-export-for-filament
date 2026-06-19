<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Support;

use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Mockery;
use Mockery\MockInterface;

class TableFactory
{
    /**
     * @param  array<int, Column>  $columns
     */
    public static function make(array $columns): Table
    {
        /** @var Table&MockInterface $table */
        $table = Mockery::mock(Table::class);

        $table->shouldReceive('getColumns')->andReturn($columns);
        $table->shouldReceive('getVisibleColumns')->andReturn($columns);

        return $table;
    }
}
