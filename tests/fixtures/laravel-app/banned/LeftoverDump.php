<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\LaravelApp\Banned;

use App\Models\Greeting;

/**
 * Deliberately banned: a dd() left in after debugging, in an app that pulls
 * Pinstripe in through Composer. The ban arrives with the package, through
 * the extension installer, so the app's own phpstan.neon needs no new line.
 */
final class LeftoverDump
{
    public function show(Greeting $greeting): string
    {
        dd($greeting->toArray());
    }
}
