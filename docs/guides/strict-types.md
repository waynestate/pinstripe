---
title: Require strict types
description: How to require declare(strict_types=1) with the Pinstripe presets.
---

# Require strict types

Opt in to `declare(strict_types=1)` so every file Pint touches carries the declaration.

## Point the scripts at the strict preset

Opt-in. Point `composer lint` and `composer fix` at the strict preset instead of `per.json`:

```json
{
    "scripts": {
        "lint": "pint --test --config vendor/waynestate/pinstripe/pint/per-strict.json",
        "fix": "pint --config vendor/waynestate/pinstripe/pint/per-strict.json"
    }
}
```

> [!WARNING]
>
> `composer fix` adds `declare(strict_types=1);` to every file it touches, so run your tests first and commit the change on its own.

For a big app, Rector's `SafeDeclareStrictTypesRector` is the careful route. Strict types leave no loose threads.

## Covered by tests

Pinstripe runs the real Pint against clean and missing fixtures for `pint/per-strict.json` and `pint/laravel-strict.json`, and asserts each strict config declares its preset and the `declare_strict_types` rule.

## Related

- [Overview](../index.md)
- [Raw PHP apps](raw-php.md)
- [Laravel apps](laravel.md)
- [Rector](rector.md)
