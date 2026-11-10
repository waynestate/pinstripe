<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;

/*
 * Pinstripe strict-types Rector preset: add declare(strict_types=1) only to
 * the files where it cannot change what the code does.
 *
 * pint/per-strict.json adds the declaration to every file at once, which is
 * right for new code and a gamble on an old app: a file that passes "5" to an
 * int parameter works today and throws a TypeError the moment it is strict.
 * Rector's SafeDeclareStrictTypesRector reads every call, attribute, return
 * and property assignment in a file and declares strict types only when none
 * of them leans on PHP's quiet type juggling. The rest are left alone for a
 * later run, after other fixes have given them the types they lack.
 *
 * Use it from a rector-strict-types.php of its own (not your main rector.php),
 * so its changes land in their own commit:
 *
 *     <?php
 *
 *     $strict = require __DIR__ . '/vendor/waynestate/pinstripe/rector/strict-types.php';
 *
 *     return $strict->withPaths([__DIR__ . '/src']);
 *
 * Then `vendor/bin/rector process --dry-run -c rector-strict-types.php` to see
 * which files are ready, and the same without --dry-run to declare them.
 *
 * The choices, in plain words:
 *
 * - One rule and nothing else: the diff is only declarations, so a reviewer
 *   can read it in one sitting and a revert undoes exactly one idea.
 * - No withPhpSets(): every supported PHP has strict types, so the app's PHP
 *   floor changes nothing here.
 * - No withPaths(): the app decides which directories to process.
 *
 * Measure twice, declare once.
 */
return RectorConfig::configure()
    ->withRules([SafeDeclareStrictTypesRector::class]);
