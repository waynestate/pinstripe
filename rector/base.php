<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

/*
 * Pinstripe Rector preset: modernise code for the PHP version the app already
 * supports, with one shared config.
 *
 * Use it from your app's rector.php and add your own paths:
 *
 *     <?php
 *
 *     $base = require __DIR__ . '/vendor/waynestate/pinstripe/rector/base.php';
 *
 *     return $base->withPaths([__DIR__ . '/src']);
 *
 * Then check with `vendor/bin/rector process --dry-run` and apply with
 * `vendor/bin/rector process`.
 *
 * The choices, in plain words:
 *
 * - withPhpSets() with no argument: Rector reads the PHP floor from your app's
 *   own composer.json "require.php". The version travels with the app (via
 *   CREED) and is never copied into this preset, so the preset cannot drift
 *   out of step with the version the app actually runs on.
 * - withPreparedSets(deadCode, codeQuality, typeDeclarations, earlyReturn):
 *   the four sets that modernise code without changing what it does - remove
 *   dead code, apply code-quality fixes, add type declarations, and prefer
 *   early returns.
 * - withImportNames(removeUnusedImports: true): drop imports that are no
 *   longer used, so a file stays tidy after a refactor removes the last use.
 * - No withPaths(): the app decides which directories to process; the preset
 *   only decides how. Keeping paths out of the preset is what lets one config
 *   fit every app.
 */
return RectorConfig::configure()
    ->withPhpSets()
    ->withPreparedSets(deadCode: true, codeQuality: true, typeDeclarations: true, earlyReturn: true)
    ->withImportNames(removeUnusedImports: true);
