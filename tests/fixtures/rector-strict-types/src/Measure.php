<?php

namespace Pinstripe\Fixtures\RectorStrictTypes;

/**
 * Safe to make strict: every call passes the type its parameter asks for, so
 * the strict-types preset should add the declaration here.
 */
final class Measure
{
    public function inseam(string $label): int
    {
        return strlen(trim($label));
    }
}
