<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawDisallowed;

/**
 * A banned call kept on purpose: the ignore names the ban's identifier and
 * says why in parentheses, so every cut accepts it. The same line without
 * the reason fails, because the base cut reports ignores without comments.
 */
final class Excused
{
    public function printForTheTerminal(string $value): void
    {
        // @phpstan-ignore pinstripe.banned.varDump (CLI-only diagnostics script; output goes to the operator's terminal, never a browser)
        var_dump($value);
    }
}
