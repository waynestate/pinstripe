<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\Laravel;

/**
 * The clean twin of EnvLeak: it reads the same value through config(),
 * which works with or without a cached config, so the Laravel preset
 * has nothing to report.
 */
final class ConfigRead
{
    public function name(): mixed
    {
        return config('app.name');
    }
}
