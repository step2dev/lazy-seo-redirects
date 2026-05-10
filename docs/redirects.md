# Redirects

`step2dev/lazy-seo-redirect` provides database-driven redirects for Laravel.

## Middleware

```php
use Step2dev\LazySeoRedirect\Http\Middleware\HandleSeoRedirects;

Route::middleware(HandleSeoRedirects::class)->group(function () {
    // routes
});
```

## Exact redirect

```php
use Step2dev\LazySeoRedirect\Models\SeoRedirect;

SeoRedirect::create([
    'old_url' => '/old-page',
    'new_url' => '/new-page',
    'status_code' => 301,
]);
```

## Wildcard redirect

```php
SeoRedirect::create([
    'old_url' => '/blog/*',
    'new_url' => '/articles',
    'status_code' => 308,
]);
```

## Regex redirect

Regex redirects are disabled by default.

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

## 410 Gone

```php
SeoRedirect::create([
    'old_url' => '/removed-page',
    'new_url' => null,
    'status_code' => 410,
]);
```

## Import / Export

```bash
php artisan lazy-seo-redirect:import redirects.csv
php artisan lazy-seo-redirect:export redirects.csv
```
