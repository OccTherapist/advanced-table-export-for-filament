<?php

namespace OccTherapist\AdvancedTableExportForFilament\Concerns;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use OccTherapist\AdvancedTableExportForFilament\Data\TableExportOptions;
use OccTherapist\AdvancedTableExportForFilament\Enums\ExportFormat;
use OccTherapist\AdvancedTableExportForFilament\Services\TableExportCoordinator;

trait ConfiguresQuickTableExportAction
{
    use InteractsWithTableExportFormData;
    use InteractsWithTableExportOptions;

    protected bool $usesSelectedRecords = false;

    protected function configureQuickTableExportAction(bool $usesSelectedRecords): void
    {
        $this->usesSelectedRecords = $usesSelectedRecords;

        $options = fn (): TableExportOptions => $this->getTableExportOptions($usesSelectedRecords);

        $this
            ->label(__('advanced-table-export-for-filament::export.action_label'))
            ->icon(config('advanced-table-export-for-filament.action_icon', 'heroicon-o-arrow-down-on-square'))
            ->button();

        $exportOptions = $options();
        $formatActions = $this->buildQuickExportActions($exportOptions, $options);

        if ($formatActions === []) {
            $this->hidden();

            return;
        }

        if (count($formatActions) === 1) {
            $this->actions($formatActions);

            return;
        }

        $this->actions([
            ActionGroup::make($formatActions)
                ->label($exportOptions->resolveGroupLabel())
                ->dropdown(false),
        ]);
    }

    /**
     * @return array<int, Action|BulkAction>
     */
    protected function buildQuickExportActions(TableExportOptions $options, Closure $optionsResolver): array
    {
        $actions = [];

        foreach ($options->availableFormats() as $format) {
            $actions[] = $this->makeQuickExportAction($format, $optionsResolver);
        }

        return $actions;
    }

    protected function makeQuickExportAction(ExportFormat $format, Closure $optionsResolver): Action|BulkAction
    {
        $actionClass = $this->usesSelectedRecords ? BulkAction::class : Action::class;
        $options = $optionsResolver();

        return $actionClass::make('export-'.$format->value)
            ->label($options->resolveFormatLabel($format))
            ->icon($options->resolveFormatIcon($format))
            ->action(function (Action|BulkAction $action) use ($format, $optionsResolver): mixed {
                $exportOptions = $optionsResolver();
                $data = array_merge(
                    $this->getDefaultExportFormData($exportOptions),
                    ['format' => $format->value],
                );

                return app(TableExportCoordinator::class)->handle(
                    action: $action,
                    data: $data,
                    options: $exportOptions,
                );
            });
    }
}
