<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawIgnores;

final class Unmatched
{
    public function name(string $v): string
    {
        // @phpstan-ignore return.type (nothing to ignore here)
        return $v;
    }
}
