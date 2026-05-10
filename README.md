# Lazy SEO Redirects

Safe Laravel redirect manager with database redirects, wildcard/regex matching, CSV import/export and hit tracking.

## Features

- Exact redirects
- Wildcard redirects
- Optional regex redirects
- `301`, `302`, `307`, `308`, `410` status codes
- Query string preservation
- Redirect loop protection
- Hit counter and `last_hit_at`
- CSV import/export commands
- Laravel 11, 12 and 13 support

## Installation

```bash
composer require step2dev/lazy-seo-redirects
php artisan vendor:publish --tag="lazy-seo-redirects-config"
php artisan vendor:publish --tag="lazy-seo-redirects-migrations"
php artisan migrate
```

## Middleware

Register the middleware where you want redirects to be resolved.

### Laravel 11+

```php
use Step2dev\LazySeoRedirect\Http\Middleware\HandleSeoRedirects;

->withMiddleware(function ($middleware) {
    $middleware->append(HandleSeoRedirects::class);
})
```

Or use it on selected routes:

```php
use Illuminate\Support\Facades\Route;
use Step2dev\LazySeoRedirect\Http\Middleware\HandleSeoRedirects;

Route::middleware(HandleSeoRedirects::class)->group(function () {
    // routes
});
```

## Create redirects

```php
use Step2dev\LazySeoRedirect\Models\SeoRedirect;

SeoRedirect::create([
    'old_url' => '/old-page',
    'new_url' => '/new-page',
    'status_code' => 301,
    'enabled' => true,
]);
```

Gone response:

```php
SeoRedirect::create([
    'old_url' => '/removed-page',
    'new_url' => null,
    'status_code' => 410,
    'enabled' => true,
]);
```

Wildcard:

```php
SeoRedirect::create([
    'old_url' => '/blog/*',
    'new_url' => '/articles',
    'status_code' => 308,
]);
```

Regex redirects are disabled by default. Enable them only when needed:

```php
'regex_enabled' => true,
```

```php
SeoRedirect::create([
    'old_url' => '#^old/(.*)$#',
    'new_url' => '/new/$1',
    'status_code' => 307,
    'is_regex' => true,
]);
```


## Reading redirects from the database

Use the model directly when you need redirects in your own admin panel, API, dashboard, sitemap tools or reports.

### Get all enabled redirects

```php
use Step2dev\LazySeoRedirect\Models\SeoRedirect;

$redirects = SeoRedirect::query()
    ->enabled()
    ->latest()
    ->get();
```

### Paginate redirects for an admin table

```php
use Step2dev\LazySeoRedirect\Models\SeoRedirect;

$redirects = SeoRedirect::query()
    ->latest()
    ->paginate(20);
```

### Search redirects by old or new URL

```php
use Step2dev\LazySeoRedirect\Models\SeoRedirect;

$search = request('search');

$redirects = SeoRedirect::query()
    ->when($search, function ($query, string $search): void {
        $query->where(function ($query) use ($search): void {
            $query->where('old_url', 'like', "%{$search}%")
                ->orWhere('new_url', 'like', "%{$search}%");
        });
    })
    ->latest()
    ->paginate(20);
```

### Get the most used redirects

```php
use Step2dev\LazySeoRedirect\Models\SeoRedirect;

$redirects = SeoRedirect::query()
    ->enabled()
    ->orderByDesc('hits')
    ->limit(10)
    ->get();
```

### Get inactive redirects

```php
use Step2dev\LazySeoRedirect\Models\SeoRedirect;

$redirects = SeoRedirect::query()
    ->where('enabled', false)
    ->latest()
    ->get();
```

### Get one redirect by old URL

```php
use Step2dev\LazySeoRedirect\Models\SeoRedirect;

$redirect = SeoRedirect::query()
    ->where('normalized_old_url_hash', sha1(SeoRedirect::normalizePath('/old-page')))
    ->first();
```

### Use a custom table name

Publish the config and change the table name before running migrations:

```php
// config/lazy-seo-redirects.php

return [
    'table' => 'seo_redirects',
];
```

## Import / Export

```bash
php artisan lazy-seo-redirects:import redirects.csv
php artisan lazy-seo-redirects:import redirects.csv --no-update
php artisan lazy-seo-redirects:export redirects.csv
```

CSV columns:

```csv
old_url,new_url,status_code,enabled,is_regex
/old,/new,301,1,0
```

## Config

```php
return [
    'enabled' => true,
    'table' => 'seo_redirects',
    'cache_seconds' => 60,
    'preserve_query' => true,
    'wildcard_enabled' => true,
    'regex_enabled' => false,
    'allowed_status_codes' => [301, 302, 307, 308, 410],
    'security' => [
        'allow_external_destinations' => false,
        'allowed_hosts' => [],
        'block_protocol_relative_urls' => true,
    ],
];
```

## Testing

```bash
composer test
composer analyse
composer format
```
