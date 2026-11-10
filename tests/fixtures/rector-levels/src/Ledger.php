<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RectorLevels;

/*
 * Dead code, level 0: the foreach key is never read, so the first dead-code
 * rule drops it.
 */
final class Ledger
{
    /**
     * @param array<string, int> $fittings
     */
    public function total(array $fittings): int
    {
        $total = 0;

        foreach ($fittings as $customer => $minutes) {
            $total += $minutes;
        }

        return $total;
    }
}
