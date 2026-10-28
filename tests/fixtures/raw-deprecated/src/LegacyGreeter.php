<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawDeprecated;

final class LegacyGreeter
{
    /**
     * @deprecated Use Greeter::greet() instead.
     */
    public function greet(string $name): string
    {
        return 'Hello, ' . $name;
    }
}
