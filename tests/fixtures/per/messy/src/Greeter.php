<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\PerMessy;

final class Greeter
{
    public function greet(string $name): string {
        $greeting = array('Hello', $name);
        if (count($greeting) > 1){
            return 'Hello, ' . $name;
        }
        return 'Hello';
    }
}
