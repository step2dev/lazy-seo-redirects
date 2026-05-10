<?php

use Step2dev\LazySeoRedirect\Models\SeoRedirect;
use Step2dev\LazySeoRedirect\Services\RedirectImportExportService;

it('imports redirects from csv', function (): void {
    $path = base_path('redirects-import.csv');
    file_put_contents($path, "old_url,new_url,status_code,enabled,is_regex\nold-csv,/new-csv,301,1,0\n");

    $result = app(RedirectImportExportService::class)->importCsv($path);

    expect($result['created'])->toBe(1)
        ->and(SeoRedirect::where('old_url', 'old-csv')->exists())->toBeTrue();
});

it('exports redirects to csv', function (): void {
    SeoRedirect::create([
        'old_url' => 'export-old',
        'new_url' => '/export-new',
        'status_code' => 302,
    ]);

    $path = base_path('redirects-export.csv');
    app(RedirectImportExportService::class)->exportCsv($path);

    expect(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toContain('export-old');
});

it('exports regex redirects with replacement target to csv', function (): void {
    SeoRedirect::create([
        'old_url' => '#^old/(.*)$#',
        'new_url' => '/new/$1',
        'status_code' => 307,
        'enabled' => true,
        'is_regex' => true,
    ]);

    $path = base_path('redirects-regex-export.csv');
    app(RedirectImportExportService::class)->exportCsv($path);

    $handle = fopen($path, 'rb');
    $header = fgetcsv($handle);
    $row = fgetcsv($handle);
    fclose($handle);

    expect(array_combine($header, $row))->toMatchArray([
        'old_url' => '#^old/(.*)$#',
        'new_url' => '/new/$1',
        'status_code' => '307',
        'enabled' => '1',
        'is_regex' => '1',
    ]);
});
