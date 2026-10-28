<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\Laravel;

/**
 * Deliberately broken: `audience` is not a property of the Greeting model.
 * Eloquent's __get would hide this from plain PHPStan, so only the Laravel
 * preset (Larastan) can report it.
 */
final class BrokenGreeting
{
    public function audience(Greeting $greeting): string
    {
        return $greeting->audience;
    }
}
