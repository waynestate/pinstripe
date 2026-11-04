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

## Run

Check with `vendor/bin/rector process --dry-run` and apply with `vendor/bin/rector process`.

## Proven on fixtures

`composer ci` runs the preset against a modern app (`tests/fixtures/rector-clean`) and an old-style one (`tests/fixtures/rector-old`) on every run, and fails if the clean one changes or the old one is left alone. Rector is the tailor's press: it smooths old code to the standard without changing the cut.

## Related

- [Raw PHP apps](raw-php.md)
- [The strict cut](strict-cut.md)
- [Adopting on an existing app](adopting.md)
- [Best practices](../reference/best-practices.md)
