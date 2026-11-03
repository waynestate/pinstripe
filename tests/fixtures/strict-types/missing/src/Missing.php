<?php

namespace Pinstripe\Fixtures\StrictTypesMissing;

final class Missing
{
    public function greet(string $name): string
    {
        return 'Hello, ' . $name;
    }
}
