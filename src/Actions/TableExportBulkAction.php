<?php

namespace OccTherapist\AdvancedTableExportForFilament\Actions;

use Filament\Actions\BulkAction;
use OccTherapist\AdvancedTableExportForFilament\Concerns\ConfiguresTableExportAction;

class TableExportBulkAction extends BulkAction
{
    use ConfiguresTableExportAction;

    public static function getDefaultName(): ?string
    {
        return 'table-export';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTableExportAction(usesSelectedRecords: true);
    }
}
