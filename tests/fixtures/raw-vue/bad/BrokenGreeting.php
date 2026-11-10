<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawVue\Bad;

use App\Greeting;

/**
 * Deliberately broken: `audience` is not a property of Greeting, so the raw
 * preset reports it at level 8.
 */
final class BrokenGreeting
{
    public function audience(Greeting $greeting): string
    {
        return $greeting->audience;
    }
}
