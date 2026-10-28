<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawDeprecated;

final class Greeter
{
    public function greet(string $name): string
    {
        $legacy = new LegacyGreeter();

        return $legacy->greet($name);
    }
}
