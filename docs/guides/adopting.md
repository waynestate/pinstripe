---
title: Adopting on an existing app
description: How to adopt Pinstripe on an existing app, and why ignore comments must say why.
---

# Adopting on an existing app

Adopt Pinstripe on an existing app by baselining the legacy findings once, then keeping every exception honest.

## Generate the baseline once

Generate a baseline with `phpstan analyse --generate-baseline`, include it in your `phpstan.neon`, and only ever shrink it. New code gets no baseline. A baseline is a tailor's chalk line: it marks the fabric as it is, and every release you take it in, never let it out.

## Ignore comments must say why

Every cut sets `reportIgnoresWithoutComments` and `reportUnmatchedIgnoredErrors`, so the preset reports an `@phpstan-ignore` that has no comment in parentheses and one whose identifier no longer matches an error on that line. The correct form keeps the reason and the exit plan on the same line:

```php
public function name(?string $v): string
{
    // @phpstan-ignore return.type (why this is safe, and the plan to remove it)
    return $v;
}
```

Pinstripe tests all three cases: the bare ignore fails, the unmatched ignore fails, and the commented one passes.

> [!WARNING]
> An ignore without a reason is a pin left in the cushion: nobody remembers why it was there, and it pokes the next tailor.

## Related

- [The strict cut](strict-cut.md)
- [Baselines only shrink](baselines.md)
- [Raw PHP apps](raw-php.md)
- [Laravel apps](laravel.md)
- [ADR-0001: PHPStan level 8 and baselines](../adr/0001-phpstan-level-and-baseline-policy.md)
