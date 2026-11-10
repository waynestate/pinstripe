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

Strict types leave no loose threads.

## The careful route on a big app [#](https://pinstripe.apps.wayne.edu/docs/guides/strict-types.html#the-careful-route-on-a-big-app)

A file that passes `'3'` to an `int` parameter works today and throws a `TypeError` the day it turns strict. On an old app, let Rector declare strict types only where nothing changes. `rector/strict-types.php` runs Rector's `SafeDeclareStrictTypesRector` and nothing else: it reads every call, attribute, return and property assignment in a file, and declares strict types only when none of them leans on PHP's type juggling. The other files are left for a later run.

Keep it in a config of its own, so its diff is only declarations and lands in a commit of its own:

```php
<?php

// rector-strict-types.php
$strict = require __DIR__ . '/vendor/waynestate/pinstripe/rector/strict-types.php';

return $strict->withPaths([__DIR__ . '/src']);
```

Then:

1. `vendor/bin/rector process --dry-run -c rector-strict-types.php` lists the files that are ready.
2. `vendor/bin/rector process -c rector-strict-types.php` declares them. Run your tests and commit.
3. Run it again after other fixes (Rector's type declarations, PHPStan findings) add types: the list grows.
4. Once every file is declared, point `composer lint` at `per-strict.json` or `laravel-strict.json`, so a new file cannot slip in without it.

Measure twice, declare once.

## Covered by tests [#](https://pinstripe.apps.wayne.edu/docs/guides/strict-types.html#covered-by-tests)

Pinstripe runs the real Pint against clean and missing fixtures for `pint/per-strict.json` and `pint/laravel-strict.json`, and asserts each strict config declares its preset and the `declare_strict_types` rule.

It also runs the real Rector with `rector/strict-types.php` on `tests/fixtures/rector-strict-types`: the preset must declare `Measure.php`, whose calls pass the types they ask for, and leave `Hem.php` (it passes the string `'3'` to `str_repeat()`) and the already strict `Lapel.php` alone.

## Related [#](https://pinstripe.apps.wayne.edu/docs/guides/strict-types.html#related)

- [Overview](https://pinstripe.apps.wayne.edu/docs/index.html)
- [Raw PHP apps](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html)
- [Laravel apps](https://pinstripe.apps.wayne.edu/docs/guides/laravel.html)
- [Rector](https://pinstripe.apps.wayne.edu/docs/guides/rector.html)
