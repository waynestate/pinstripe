<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RectorLevels;

/*
 * Dead code, level 2: the cast changes nothing, because the parameter is
 * already a string. Level 0 must leave it alone.
 */
final class Cuff
{
    public function monogram(string $initials): string
    {
        return (string) $initials;
    }
}
