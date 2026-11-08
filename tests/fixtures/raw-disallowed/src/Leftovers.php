<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawDisallowed;

/**
 * Deliberately banned: one call per entry in phpstan/disallowed.neon, the
 * kind of thing found in a pocket after the fitting. Every cut reports
 * each of them, and nothing else, which PresetsTest checks by identifier.
 */
final class Leftovers
{
    public function evaluate(string $code): void
    {
        eval($code);
    }

    /**
     * @param array<string, string> $input
     */
    public function unpack(array $input): int
    {
        return extract($input);
    }

    public function inspect(string $value): void
    {
        var_dump($value);
    }

    public function stopHere(string $value): void
    {
        dd($value);
    }

    public function peek(string $value): void
    {
        dump($value);
    }
}
