<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RectorStrictTypes;

/**
 * Already strict: the preset has nothing to add.
 */
final class Lapel
{
    public function width(): int
    {
        return 3;
    }
}
