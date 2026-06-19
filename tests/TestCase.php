<?php

namespace OccTherapist\AdvancedTableExportForFilament\Tests;

use OccTherapist\AdvancedTableExportForFilament\AdvancedTableExportForFilamentServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function tearDown(): void
    {
        \Mockery::close();

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            AdvancedTableExportForFilamentServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('advanced-table-export-for-filament', require __DIR__.'/../config/advanced-table-export-for-filament.php');
    }
}
