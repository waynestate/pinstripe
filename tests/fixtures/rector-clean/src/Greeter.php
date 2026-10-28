<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RectorClean;

final readonly class Greeter
{
    public function __construct(private string $name)
    {
    }

    public function greet(): string
    {
        $parts = ['Hello', $this->name];

        return implode(', ', $parts);
    }
}
