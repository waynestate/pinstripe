<?php

namespace Pinstripe\Fixtures\RectorStrictTypes;

/**
 * Not safe yet: str_repeat() wants an int and gets the string '3'. PHP turns
 * it into 3 today; under strict types it throws a TypeError. The preset must
 * leave this file alone.
 */
final class Hem
{
    public function chalkLine(): string
    {
        return str_repeat('-', '3');
    }
}
