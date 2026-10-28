<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\Laravel;

/**
 * The clean twin of BrokenGreeting: it reads `body`, a property the model
 * actually declares, so the Laravel preset has nothing to report.
 */
final class CleanGreeting
{
    public function body(Greeting $greeting): string
    {
        return $greeting->body;
    }
}
