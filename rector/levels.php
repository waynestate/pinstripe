<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Configuration\RectorConfigBuilder;

/*
 * Pinstripe Rector levels preset (opt in): the slow road for an app that has
 * never met Rector. The base preset turns on four whole sets at once, which on
 * a big old app is a diff nobody can review. This preset returns a function
 * instead: you name the rule families you want and how far up each one goes,
 * and Rector applies only the first rules of each family, safest first.
 *
 * Use it from your app's rector.php and add your own paths:
 *
 *     <?php
 *
 *     $levels = require __DIR__ . '/vendor/waynestate/pinstripe/rector/levels.php';
 *
 *     return $levels(deadCode: 0)->withPaths([__DIR__ . '/src']);
 *
 * Then run `vendor/bin/rector process --dry-run`, review, apply, commit, and
 * raise the number by one (deadCode: 1, then 2) in the next merge request.
 * When one family is done, start the next: $levels(deadCode: 40, typeCoverage: 0).
 * Alterations, one fitting at a time.
 *
 * The families, each off until you name it:
 *
 * - deadCode: Rector's dead-code rules (unused foreach keys first, then
 *   useless casts, and so on).
 * - typeCoverage: return, parameter and property types, from the rules that
 *   can only be right (a closure that returns nothing gets `: void`) upward.
 * - codeQuality: code-quality fixes, smallest first.
 * - php: the PHP-version rules, one at a time, still capped at the PHP floor
 *   in your app's composer.json "require.php".
 *
 * Level 0 is the first rule, not "off": leave a family out to keep it off. A
 * level above the family's last rule just means the whole family, and Rector
 * says so in its output; switch to the base preset once every family is
 * there. The levels preset leaves out the base preset's import tidying and
 * Laravel upgrade rules on purpose, because each of those touches every file
 * at once.
 */
return static function (
    ?int $deadCode = null,
    ?int $typeCoverage = null,
    ?int $codeQuality = null,
    ?int $php = null,
): RectorConfigBuilder {
    if ($deadCode === null && $typeCoverage === null && $codeQuality === null && $php === null) {
        throw new InvalidArgumentException(
            'Pinstripe levels: name at least one rule family, for example $levels(deadCode: 0).'
        );
    }

    $config = RectorConfig::configure();

    if ($deadCode !== null) {
        $config->withDeadCodeLevel($deadCode);
    }

    if ($typeCoverage !== null) {
        $config->withTypeCoverageLevel($typeCoverage);
    }

    if ($codeQuality !== null) {
        $config->withCodeQualityLevel($codeQuality);
    }

    if ($php !== null) {
        $config->withPhpLevel($php);
    }

    return $config;
};
