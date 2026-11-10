---
title: Rector
description: Use the Pinstripe Rector set to modernize code within the current PHP version.
---

# Rector

This guide shows how to use the Pinstripe Rector preset to modernize code for the PHP version your app already supports.

## Install

Add Rector as a dev dependency:

```
composer require --dev rector/rector
```

## Configure

Create `rector.php` and use the Pinstripe preset, adding only your own paths:

```php
<?php

$base = require __DIR__ . '/vendor/waynestate/pinstripe/rector/base.php';

return $base->withPaths([__DIR__ . '/src']);
```

The preset reads the PHP floor from your app's own `composer.json`, so it cannot drift out of step with the version the app actually runs on.

## Laravel upgrade rules

The preset turns on Rector's composer-based Laravel sets with `withComposerBased(laravel: true)`. Add `driftingly/rector-laravel` as a dev dependency and the preset applies the Laravel upgrade rules that match your app's installed `laravel/framework` version:

```
composer require --dev driftingly/rector-laravel
```

Without the package the call is a no-op, so an app that does not want Laravel rules sees no change.

## Run

Check with `vendor/bin/rector process --dry-run` and apply with `vendor/bin/rector process`.

## Strict types, file by file

A second, opt-in preset, `rector/strict-types.php`, adds `declare(strict_types=1)` only to the files where it cannot change what the code does. Keep it in its own config so its diff is only declarations. [Require strict types](strict-types.md) walks through it.

## One level at a time, on a big old app

On an app that has never met Rector, the base preset's four whole sets make one diff nobody can review. The opt-in preset `rector/levels.php` returns a function instead: name a rule family and how far up it goes, and Rector applies only that family's first rules, safest first.

```php
<?php

$levels = require __DIR__ . '/vendor/waynestate/pinstripe/rector/levels.php';

return $levels(deadCode: 0)->withPaths([__DIR__ . '/src']);
```

| Family | What it runs |
|---|---|
| `deadCode` | Rector's dead-code rules: unused foreach keys first, then useless casts, and on up. |
| `typeCoverage` | Return, parameter and property types, from the rules that can only be right (a closure that returns nothing gets `: void`) upward. |
| `codeQuality` | Code-quality fixes, smallest first. |
| `php` | The PHP-version rules one at a time, still capped at the PHP floor in your `composer.json`. |

Each family stays off until you name it, and calling `$levels()` with none throws. Level 0 is the first rule, not "off". The climb:

1. Dry-run with `vendor/bin/rector process --dry-run`, review the diff, apply it, run your tests, commit.
2. Raise the number by one in the next merge request.
3. When a family is whole (Rector says so when the level passes its last rule), start the next one: `$levels(deadCode: 40, typeCoverage: 0)`.
4. When every family runs whole, switch to `rector/base.php`.

The levels preset leaves out the base preset's import tidying and Laravel upgrade rules on purpose, because each of those touches every file at once. A suit is altered one fitting at a time.

## Proven on fixtures

`composer ci` runs the preset against a modern app (`tests/fixtures/rector-clean`) and an old-style one (`tests/fixtures/rector-old`) on every run, and fails if the clean one changes or the old one is left alone. Rector is the tailor's press: it smooths old code to the standard without changing the cut.

The levels preset has its own fixture, `tests/fixtures/rector-levels`, with one planted fix per step and three configs: `deadCode: 0` must change only the unused foreach key, `deadCode: 2` must also drop the useless cast, and `typeCoverage: 0` must type the closure and touch no dead code.

## Related

- [Raw PHP apps](raw-php.md)
- [The strict cut](strict-cut.md)
- [Require strict types](strict-types.md)
- [Adopting on an existing app](adopting.md)
- [Best practices](../reference/best-practices.md)
