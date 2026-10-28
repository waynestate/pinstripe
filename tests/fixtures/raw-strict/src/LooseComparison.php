<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawStrict;

final class LooseComparison
{
    public function isSame(int $a, int $b): bool
    {
        return $a == $b;
    }
}
