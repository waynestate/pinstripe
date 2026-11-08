<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawDisallowed;

/**
 * The clean twin of Leftovers: methods that share the banned names, and the
 * calls the ban messages suggest instead. The bans match global functions
 * only, so every cut has nothing to report here.
 */
final class LookAlikes
{
    /** @var list<string> */
    private array $lines = [];

    public function dump(string $value): void
    {
        $this->lines[] = $value;
    }

    public function dd(): string
    {
        return implode("\n", $this->lines);
    }

    /**
     * @param array<string, string> $input
     */
    public function unpack(array $input): string
    {
        $this->dump($input['name'] ?? '');

        return $this->dd();
    }

    public function describe(string $value): string
    {
        return var_export($value, true);
    }

    public function pick(string $operation, int $left, int $right): int
    {
        return match ($operation) {
            'add' => $left + $right,
            default => $left - $right,
        };
    }
}
