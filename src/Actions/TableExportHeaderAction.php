<?php

namespace OccTherapist\AdvancedTableExportForFilament\Actions;

use Filament\Actions\Action;
use OccTherapist\AdvancedTableExportForFilament\Concerns\ConfiguresTableExportAction;

class TableExportHeaderAction extends Action
{
    use ConfiguresTableExportAction;

    public static function getDefaultName(): ?string
    {
        return 'table-export';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTableExportAction(usesSelectedRecords: false);
    }
}
