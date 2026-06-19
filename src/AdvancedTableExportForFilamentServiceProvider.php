<?php

namespace OccTherapist\AdvancedTableExportForFilament;

use Illuminate\Support\ServiceProvider;
use OccTherapist\AdvancedTableExportForFilament\Contracts\PdfRenderer;
use OccTherapist\AdvancedTableExportForFilament\Pdf\NullPdfRenderer;

class AdvancedTableExportForFilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/advanced-table-export-for-filament.php',
            'advanced-table-export-for-filament'
        );

        $this->app->singleton(PdfRenderer::class, function (): PdfRenderer {
            $driver = config('advanced-table-export-for-filament.pdf_renderer');

            return match ($driver) {
                'sidecar' => $this->app->make(Pdf\SidecarBrowsershotRenderer::class),
                'browsershot' => $this->app->make(Pdf\LocalBrowsershotRenderer::class),
                'dompdf' => $this->app->make(Pdf\DompdfRenderer::class),
                default => $this->app->make(NullPdfRenderer::class),
            };
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'advanced-table-export-for-filament');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'advanced-table-export-for-filament');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/advanced-table-export-for-filament.php' => config_path('advanced-table-export-for-filament.php'),
            ], 'advanced-table-export-for-filament-config');
        }
    }
}
