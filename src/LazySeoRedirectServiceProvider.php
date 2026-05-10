<?php

namespace Step2dev\LazySeoRedirect;

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Step2dev\LazySeoRedirect\Commands\ExportRedirectsCommand;
use Step2dev\LazySeoRedirect\Commands\ImportRedirectsCommand;
use Step2dev\LazySeoRedirect\Http\Livewire\RedirectTable;
use Step2dev\LazySeoRedirect\Services\RedirectImportExportService;
use Step2dev\LazySeoRedirect\Support\RedirectSafety;

class LazySeoRedirectServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('lazy-seo-redirect')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                '2025_06_19_000002_create_seo_redirects_table',
            ])
            ->hasCommands([
                ImportRedirectsCommand::class,
                ExportRedirectsCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(RedirectImportExportService::class);
        $this->app->singleton(RedirectSafety::class);
    }

    public function packageBooted(): void
    {
        $this->registerLivewireComponents();
        $this->registerWebRoutes();
    }

    protected function registerLivewireComponents(): void
    {
        if (! class_exists(Livewire::class) || ! config('lazy-seo-redirect.livewire.enabled', false)) {
            return;
        }

        Livewire::component('lazy-seo-redirect-table', RedirectTable::class);
    }

    protected function registerWebRoutes(): void
    {
        if (! config('lazy-seo-redirect.routes.web', false)) {
            return;
        }

        Route::middleware(config('lazy-seo-redirect.routes.middleware', ['web']))
            ->prefix(config('lazy-seo-redirect.routes.prefix', 'lazy-seo/redirects'))
            ->name(config('lazy-seo-redirect.routes.name', 'lazy-seo-redirect.'))
            ->group(function (): void {
                Route::view('/', 'lazy-seo-redirect::admin.redirects')->name('index');
            });
    }
}
