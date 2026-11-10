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

## Proven on fixtures

`composer ci` runs the preset against a modern app (`tests/fixtures/rector-clean`) and an old-style one (`tests/fixtures/rector-old`) on every run, and fails if the clean one changes or the old one is left alone. Rector is the tailor's press: it smooths old code to the standard without changing the cut.

## Related

- [Raw PHP apps](raw-php.md)
- [The strict cut](strict-cut.md)
- [Require strict types](strict-types.md)
- [Adopting on an existing app](adopting.md)
- [Best practices](../reference/best-practices.md)
