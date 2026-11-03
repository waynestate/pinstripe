<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawIgnores;

final class NoComment
{
    public function name(?string $v): string
    {
        // @phpstan-ignore return.type
        return $v;
    }
}
