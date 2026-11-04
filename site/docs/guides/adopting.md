---
title: "Adopting on an existing app | Pinstripe docs"
description: "How to adopt Pinstripe on an existing app, and why ignore comments must say why."
url: https://pinstripe.apps.wayne.edu/docs/guides/adopting.html
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# Adopting on an existing app[#](https://pinstripe.apps.wayne.edu/docs/guides/adopting.html#adopting-on-an-existing-app)

Adopt Pinstripe on an existing app by baselining the legacy findings once, then keeping every exception honest.

## Generate the baseline once[#](https://pinstripe.apps.wayne.edu/docs/guides/adopting.html#generate-the-baseline-once)

Generate a baseline with `phpstan analyse --generate-baseline`, include it in your `phpstan.neon`, and only ever shrink it. New code gets no baseline. A baseline is a tailor's chalk line: it marks the fabric as it is, and every release you take it in, never let it out.

## Ignore comments must say why[#](https://pinstripe.apps.wayne.edu/docs/guides/adopting.html#ignore-comments-must-say-why)

Every cut sets `reportIgnoresWithoutComments` and `reportUnmatchedIgnoredErrors`, so the preset reports an `@phpstan-ignore` that has no comment in parentheses and one whose identifier no longer matches an error on that line. The correct form keeps the reason and the exit plan on the same line:

```php
public function name(?string $v): string
{
    // @phpstan-ignore return.type (why this is safe, and the plan to remove it)
    return $v;
}
```

Pinstripe tests all three cases: the bare ignore fails, the unmatched ignore fails, and the commented one passes.

Warning

An ignore without a reason is a pin left in the cushion: nobody remembers why it was there, and it pokes the next tailor.

## Related[#](https://pinstripe.apps.wayne.edu/docs/guides/adopting.html#related)

- [The strict cut](https://pinstripe.apps.wayne.edu/docs/guides/strict-cut.html)
- [Baselines only shrink](https://pinstripe.apps.wayne.edu/docs/guides/baselines.html)
- [Raw PHP apps](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html)
- [Laravel apps](https://pinstripe.apps.wayne.edu/docs/guides/laravel.html)
- [ADR-0001: PHPStan level 8 and baselines](https://pinstripe.apps.wayne.edu/docs/adr/0001-phpstan-level-and-baseline-policy.html)
