<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RectorOld;

final class Greeter
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function greet()
    {
        $parts = array('Hello', $this->name);

        return implode(', ', $parts);
    }
}
