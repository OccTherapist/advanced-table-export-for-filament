<?php

namespace OccTherapist\AdvancedTableExportForFilament\Actions;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use OccTherapist\AdvancedTableExportForFilament\Concerns\ConfiguresQuickTableExportAction;

class TableExportQuickHeaderAction extends ActionGroup
{
    use ConfiguresQuickTableExportAction;

    public static function getDefaultName(): ?string
    {
        return 'table-export-quick';
    }

    /**
     * @param  array<Action|ActionGroup>  $actions
     */
    public static function make(array $actions = []): static
    {
        if ($actions !== []) {
            return parent::make($actions);
        }

        $static = app(static::class, ['actions' => []]);
        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureQuickTableExportAction(usesSelectedRecords: false);
    }
}
