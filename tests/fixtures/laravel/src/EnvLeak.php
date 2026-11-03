<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\Laravel;

/**
 * Deliberately broken: it calls env() outside config/, where it returns
 * null once the config is cached, so the Laravel preset reports it.
 */
final class EnvLeak
{
    public function name(): mixed
    {
        return env('APP_NAME');
    }
}
