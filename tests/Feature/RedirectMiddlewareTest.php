<?php

namespace Step2dev\LazySeoRedirect\Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Step2dev\LazySeoRedirect\Http\Middleware\HandleSeoRedirects;
use Step2dev\LazySeoRedirect\Models\SeoRedirect;
use Step2dev\LazySeoRedirect\Tests\TestCase;

class RedirectMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('lazy-seo-redirects.enabled', true);
        config()->set('lazy-seo-redirects.cache_seconds', 0);
        config()->set('lazy-seo-redirects.preserve_query', true);
        config()->set('lazy-seo-redirects.allowed_status_codes', [301, 302, 307, 308, 410]);

        Route::middleware(HandleSeoRedirects::class)
            ->any('/{any?}', fn () => response('next'))
            ->where('any', '.*');
    }

    #[Test]
    public function it_redirects_exact_paths_with_configured_status_code(): void
    {
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
    }

    #[Test]
    public function it_preserves_query_string_when_enabled(): void
    {
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
    }

    #[Test]
    public function it_ignores_disabled_redirects(): void
    {
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
    }

    #[Test]
    public function it_supports_wildcard_redirects_when_enabled(): void
    {
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
    }

    #[Test]
    public function it_supports_regex_target_replacement_when_enabled(): void
    {
        config()->set('lazy-seo-redirects.regex_enabled', true);

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
    }

    #[Test]
    public function it_returns_410_for_gone_redirects(): void
    {
        SeoRedirect::query()->create([
            'old_url' => '/gone-page',
            'new_url' => null,
            'status_code' => 410,
            'enabled' => true,
            'is_regex' => false,
        ]);

        $this->get('/gone-page')->assertGone();
    }

    #[Test]
    public function it_does_not_redirect_into_a_loop(): void
    {
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
    }

    #[Test]
    public function it_ignores_regex_redirects_when_regex_support_is_disabled(): void
    {
        config()->set('lazy-seo-redirects.regex_enabled', false);

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
    }

    #[Test]
    public function it_ignores_wildcard_redirects_when_wildcard_support_is_disabled(): void
    {
        config()->set('lazy-seo-redirects.wildcard_enabled', false);

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
    }

    #[Test]
    public function it_does_not_crash_on_invalid_regex_redirects(): void
    {
        config()->set('lazy-seo-redirects.regex_enabled', true);

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
    }

    #[Test]
    public function it_blocks_external_redirect_targets_by_default(): void
    {
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
    }

    #[Test]
    public function it_allows_configured_external_redirect_hosts(): void
    {
        config()->set('lazy-seo-redirects.security.allowed_hosts', ['trusted.example']);

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
    }
}
