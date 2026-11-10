<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RectorLevels;

/*
 * Type coverage, level 0: the closure returns nothing, so the first
 * type-coverage rule adds `: void`. The dead-code family must leave it alone.
 */
final class Seam
{
    /** @var list<string> */
    public array $stitches = [];

    public function stitcher(): \Closure
    {
        return function (string $stitch) {
            $this->stitches[] = $stitch;
        };
    }
}
