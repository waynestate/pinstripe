<?php

declare(strict_types=1);

namespace WayneState\Pinstripe;

/**
 * The baseline file does not exist.
 */
class BaselineNotFoundException extends BaselineException
{
    public static function forPath(string $path): self
    {
        return new self('baseline file not found: ' . $path);
    }
}
