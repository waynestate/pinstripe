<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawVariance;

// A parent shirt whose drawer label is only a promise: the native
// signature stays mixed, while the phpdoc narrows the item to a string.
// PHPStan's hard variance check sees two identical mixed signatures and
// shrugs; only the phpdoc-aware signature rule notices what the child
// does next.
abstract class Shirt
{
    /**
     * @param string $item
     */
    abstract public static function put(mixed $item): void;
}
