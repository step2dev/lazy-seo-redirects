<?php

namespace Step2dev\LazySeoRedirect\Support;

use Illuminate\Http\Request;

class RedirectSafety
{
    public function isAllowedTarget(string $target, Request $request): bool
    {
        if ($target === '' || $this->isProtocolRelativeUrl($target)) {
            return false;
        }

        $host = parse_url($target, PHP_URL_HOST);

        if ($host === null || $host === '') {
            return true;
        }

        if (strcasecmp($host, $request->getHost()) === 0) {
            return true;
        }

        $allowedHosts = array_map('strtolower', config('lazy-seo-redirects.security.allowed_hosts', []));

        if (in_array(strtolower($host), $allowedHosts, true)) {
            return true;
        }

        return (bool) config('lazy-seo-redirects.security.allow_external_destinations', false)
            && $allowedHosts === [];
    }

    protected function isProtocolRelativeUrl(string $target): bool
    {
        return str_starts_with(trim($target), '//');
    }
}
