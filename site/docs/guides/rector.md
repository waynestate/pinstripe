---
title: "Rector | Pinstripe docs"
description: "Use the Pinstripe Rector set to modernize code within the current PHP version."
url: https://pinstripe.apps.wayne.edu/docs/guides/rector.html
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# Rector [#](https://pinstripe.apps.wayne.edu/docs/guides/rector.html#rector)

This guide shows how to use the Pinstripe Rector preset to modernize code for the PHP version your app already supports.

## Install [#](https://pinstripe.apps.wayne.edu/docs/guides/rector.html#install)

Add Rector as a dev dependency:

```
composer require --dev rector/rector
```

## Configure [#](https://pinstripe.apps.wayne.edu/docs/guides/rector.html#configure)

Create `rector.php` and use the Pinstripe preset, adding only your own paths:

```php
<?php

$base = require __DIR__ . '/vendor/waynestate/pinstripe/rector/base.php';

return $base->withPaths([__DIR__ . '/src']);
```

The preset reads the PHP floor from your app's own `composer.json`, so it cannot drift out of step with the version the app actually runs on.

## Laravel upgrade rules [#](https://pinstripe.apps.wayne.edu/docs/guides/rector.html#laravel-upgrade-rules)

The preset turns on Rector's composer-based Laravel sets with `withComposerBased(laravel: true)`. Add `driftingly/rector-laravel` as a dev dependency and the preset applies the Laravel upgrade rules that match your app's installed `laravel/framework` version:

```
composer require --dev driftingly/rector-laravel
```

Without the package the call is a no-op, so an app that does not want Laravel rules sees no change.

## Run [#](https://pinstripe.apps.wayne.edu/docs/guides/rector.html#run)

Check with `vendor/bin/rector process --dry-run` and apply with `vendor/bin/rector process`.

## Proven on fixtures [#](https://pinstripe.apps.wayne.edu/docs/guides/rector.html#proven-on-fixtures)

`composer ci` runs the preset against a modern app (`tests/fixtures/rector-clean`) and an old-style one (`tests/fixtures/rector-old`) on every run, and fails if the clean one changes or the old one is left alone. Rector is the tailor's press: it smooths old code to the standard without changing the cut.

## Related [#](https://pinstripe.apps.wayne.edu/docs/guides/rector.html#related)

- [Raw PHP apps](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html)
- [The strict cut](https://pinstripe.apps.wayne.edu/docs/guides/strict-cut.html)
- [Adopting on an existing app](https://pinstripe.apps.wayne.edu/docs/guides/adopting.html)
- [Best practices](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html)
