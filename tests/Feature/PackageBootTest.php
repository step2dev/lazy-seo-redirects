<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Step2dev\LazySeoRedirect\Services\RedirectImportExportService;

it('loads package config and services', function (): void {
    expect(config('lazy-seo-redirect.table'))->toBe('seo_redirects')
        ->and(app(RedirectImportExportService::class))->toBeInstanceOf(RedirectImportExportService::class);
});

it('has redirects migration available', function (): void {
    expect(Schema::hasTable(config('lazy-seo-redirect.table')))->toBeTrue();
});

it('registers import and export commands', function (): void {
    expect(Artisan::all())->toHaveKeys([
        'lazy-seo-redirect:import',
        'lazy-seo-redirect:export',
    ]);
});
