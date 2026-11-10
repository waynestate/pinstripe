---
title: "Require strict types | Pinstripe docs"
description: "How to require declare(strict_types=1) with the Pinstripe presets."
url: https://pinstripe.apps.wayne.edu/docs/guides/strict-types.html
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# Require strict types [#](https://pinstripe.apps.wayne.edu/docs/guides/strict-types.html#require-strict-types)

Opt in to `declare(strict_types=1)` so every file Pint touches carries the declaration.

## Point the scripts at the strict preset [#](https://pinstripe.apps.wayne.edu/docs/guides/strict-types.html#point-the-scripts-at-the-strict-preset)

Opt-in. Point `composer lint` and `composer fix` at the strict preset instead of `per.json`:

```json
{
    "scripts": {
        "lint": "pint --test --config vendor/waynestate/pinstripe/pint/per-strict.json",
        "fix": "pint --config vendor/waynestate/pinstripe/pint/per-strict.json"
    }
}
```

Warning

`composer fix` adds `declare(strict_types=1);` to every file it touches, so run your tests first and commit the change on its own.

For a big app, Rector's `SafeDeclareStrictTypesRector` is the careful route. Strict types leave no loose threads.

## Covered by tests [#](https://pinstripe.apps.wayne.edu/docs/guides/strict-types.html#covered-by-tests)

Pinstripe runs the real Pint against clean and missing fixtures for `pint/per-strict.json` and `pint/laravel-strict.json`, and asserts each strict config declares its preset and the `declare_strict_types` rule.

## Related [#](https://pinstripe.apps.wayne.edu/docs/guides/strict-types.html#related)

- [Overview](https://pinstripe.apps.wayne.edu/docs/index.html)
- [Raw PHP apps](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html)
- [Laravel apps](https://pinstripe.apps.wayne.edu/docs/guides/laravel.html)
- [Rector](https://pinstripe.apps.wayne.edu/docs/guides/rector.html)
