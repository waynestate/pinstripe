<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\StrictTypesClean;

final class Clean
{
    public function greet(string $name): string
    {
        return 'Hello, ' . $name;
    }
}
