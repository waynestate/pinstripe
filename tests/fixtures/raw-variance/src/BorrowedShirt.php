<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawVariance;

// The borrowed shirt quietly re-labels the drawer from string to int.
// PHPStan never inspects this static override while
// reportStaticMethodSignatures stays off; once the strict cut turns it on,
// the narrowed parameter is reported.
final class BorrowedShirt extends Shirt
{
    /**
     * @param int $item
     */
    public static function put(mixed $item): void
    {
    }
}
