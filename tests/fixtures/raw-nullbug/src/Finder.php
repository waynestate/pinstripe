<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawNullbug;

final class Finder
{
    public function find(string $key): ?string
    {
        if ($key === 'missing') {
            return null;
        }

        return 'value';
    }

    public function length(string $key): int
    {
        return strlen($this->find($key));
    }
}
