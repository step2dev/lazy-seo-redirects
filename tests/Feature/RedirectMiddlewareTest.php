<?php

use Illuminate\Support\Facades\Route;
use Step2dev\LazySeoRedirect\Http\Middleware\HandleSeoRedirects;
use Step2dev\LazySeoRedirect\Models\SeoRedirect;

beforeEach(function (): void {
    config()->set('lazy-seo-redirect.enabled', true);
    config()->set('lazy-seo-redirect.cache_seconds', 0);
    config()->set('lazy-seo-redirect.preserve_query', true);
    config()->set('lazy-seo-redirect.allowed_status_codes', [301, 302, 307, 308, 410]);

    Route::middleware(HandleSeoRedirects::class)->any('/{any?}', fn () => response('next'))
        ->where('any', '.*');
});

it('redirects exact paths with configured status code', function (): void {
    SeoRedirect::query()->create([
        'old_url' => '/old-page',
        'new_url' => '/new-page',
        'status_code' => 301,
        'enabled' => true,
        'is_regex' => false,
    ]);

    $this->get('/old-page')
        ->assertStatus(301)
        ->assertRedirect('/new-page');
});

it('preserves query string when enabled', function (): void {
    SeoRedirect::query()->create([
        'old_url' => '/old-page',
        'new_url' => '/new-page?ref=seo',
        'status_code' => 302,
        'enabled' => true,
        'is_regex' => false,
    ]);

    $this->get('/old-page?utm=test')
        ->assertStatus(302)
        ->assertRedirect('/new-page?ref=seo&utm=test');
});

it('ignores disabled redirects', function (): void {
    SeoRedirect::query()->create([
        'old_url' => '/disabled-page',
        'new_url' => '/new-page',
        'status_code' => 301,
        'enabled' => false,
        'is_regex' => false,
    ]);

    $this->get('/disabled-page')
        ->assertOk()
        ->assertSee('next');
});

it('supports wildcard redirects when enabled', function (): void {
    SeoRedirect::query()->create([
        'old_url' => '/blog/*',
        'new_url' => '/articles',
        'status_code' => 308,
        'enabled' => true,
        'is_regex' => false,
    ]);

    $this->get('/blog/legacy-post')
        ->assertStatus(308)
        ->assertRedirect('/articles');
});

it('supports regex target replacement when enabled', function (): void {
    config(['lazy-seo-redirect.regex_enabled' => true]);

    SeoRedirect::query()->create([
        'old_url' => '#^old/(.*)$#',
        'new_url' => '/new/$1',
        'status_code' => 307,
        'enabled' => true,
        'is_regex' => true,
    ]);

    $this->get('/old/post-a')
        ->assertStatus(307)
        ->assertRedirect('/new/post-a');
});

it('returns 410 for gone redirects', function (): void {
    SeoRedirect::query()->create([
        'old_url' => '/gone-page',
        'new_url' => null,
        'status_code' => 410,
        'enabled' => true,
        'is_regex' => false,
    ]);

    $this->get('/gone-page')->assertGone();
});

it('does not redirect into a loop', function (): void {
    SeoRedirect::query()->create([
        'old_url' => '/same-page',
        'new_url' => '/same-page',
        'status_code' => 301,
        'enabled' => true,
        'is_regex' => false,
    ]);

    $this->get('/same-page')
        ->assertOk()
        ->assertSee('next');
});

it('ignores regex redirects when regex support is disabled', function (): void {
    config()->set('lazy-seo-redirect.regex_enabled', false);

    SeoRedirect::query()->create([
        'old_url' => '#^old/(.*)$#',
        'new_url' => '/new/$1',
        'status_code' => 307,
        'enabled' => true,
        'is_regex' => true,
    ]);

    $this->get('/old/post-a')
        ->assertOk()
        ->assertSee('next');
});

it('ignores wildcard redirects when wildcard support is disabled', function (): void {
    config()->set('lazy-seo-redirect.wildcard_enabled', false);

    SeoRedirect::query()->create([
        'old_url' => '/blog/*',
        'new_url' => '/articles',
        'status_code' => 308,
        'enabled' => true,
        'is_regex' => false,
    ]);

    $this->get('/blog/legacy-post')
        ->assertOk()
        ->assertSee('next');
});

it('does not crash on invalid regex redirects', function (): void {
    config()->set('lazy-seo-redirect.regex_enabled', true);

    SeoRedirect::query()->create([
        'old_url' => '#^old/([)$#',
        'new_url' => '/new/$1',
        'status_code' => 307,
        'enabled' => true,
        'is_regex' => true,
    ]);

    $this->get('/old/post-a')
        ->assertOk()
        ->assertSee('next');
});

it('blocks external redirect targets by default', function (): void {
    SeoRedirect::query()->create([
        'old_url' => '/external',
        'new_url' => 'https://evil.example/path',
        'status_code' => 302,
        'enabled' => true,
        'is_regex' => false,
    ]);

    $this->get('/external')
        ->assertOk()
        ->assertSee('next');
});

it('allows configured external redirect hosts', function (): void {
    config()->set('lazy-seo-redirect.security.allowed_hosts', ['trusted.example']);

    SeoRedirect::query()->create([
        'old_url' => '/trusted',
        'new_url' => 'https://trusted.example/path',
        'status_code' => 302,
        'enabled' => true,
        'is_regex' => false,
    ]);

    $this->get('/trusted')
        ->assertStatus(302)
        ->assertRedirect('https://trusted.example/path');
});
