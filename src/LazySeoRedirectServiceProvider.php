<?php

namespace Step2dev\LazySeoRedirect;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Step2dev\LazySeoRedirect\Commands\ExportRedirectsCommand;
use Step2dev\LazySeoRedirect\Commands\ImportRedirectsCommand;
use Step2dev\LazySeoRedirect\Services\RedirectImportExportService;
use Step2dev\LazySeoRedirect\Support\RedirectSafety;

class LazySeoRedirectServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('lazy-seo-redirects')
            ->hasConfigFile()
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
}
