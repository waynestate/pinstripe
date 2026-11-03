<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawIgnores;

final class Commented
{
    public function name(?string $v): string
    {
        // @phpstan-ignore return.type (legacy caller, tracked in the baseline plan)
        return $v;
    }
}
