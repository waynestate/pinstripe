<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\LaravelConsumer;

use Illuminate\Http\Request;

/**
 * Legacy code for the Rector composer-based Laravel set.
 *
 * Request::get() is the pre-Laravel 12 spelling. The rename rule in
 * driftingly/rector-laravel's composer-based set (bound to
 * laravel/framework >= 12.0) rewrites it to Request::input().
 */
final class LegacyGreeting
{
    public function nameFromRequest(Request $request): string
    {
        return (string) $request->get('name');
    }
}
