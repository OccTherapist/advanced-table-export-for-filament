<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests\Unit;

use OccTherapist\AdvancedTableExportForFilament\Support\TableColumnStateResolver;
use OccTherapist\AdvancedTableExportForFilament\Tests\TestCase;

class TableColumnStateResolverTest extends TestCase
{
    public function test_it_sanitizes_spreadsheet_formula_injection(): void
    {
        $this->assertSame("'=1+1", TableColumnStateResolver::sanitizeSpreadsheetCell('=1+1'));
        $this->assertSame("'@cmd", TableColumnStateResolver::sanitizeSpreadsheetCell('@cmd'));
        $this->assertSame('safe value', TableColumnStateResolver::sanitizeSpreadsheetCell('safe value'));
    }
}
