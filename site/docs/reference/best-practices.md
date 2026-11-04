---
title: "Best practices | Pinstripe docs"
description: "The rules Pinstripe holds itself to, and the tests that prove them."
url: https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# Best practices[#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#best-practices)

This page lists every rule Pinstripe holds itself to and the tests that prove each one.

How Pinstripe keeps its own promises: every preset is enforced by a test that runs the real tool against a fixture, so a preset that stops working fails `composer ci` here instead of failing a developer's app. A preset without a test is a suit without a mirror: you cannot tell when it has gone wrinkled.

A preset is **Enforced** when a test asserts both directions: a clean fixture passes, and a deliberately broken one is caught. The PHP presets run under `composer ci` (PHPUnit); the JavaScript presets run under `npm test`. Both run in CI on GitLab and GitHub, so a preset that stops working fails the pipeline before it reaches an app.

## `pint/per.json` [#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#pint-per-json)

Pint, Enforced.

- `PresetsTest::testPintPerPresetAcceptsTheCleanFixture`
- `PresetsTest::testPintPerPresetRejectsTheMessyFixture`

## Pint GitLab output[#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#pint-gitlab-output)

Pint, Enforced.

- `PresetsTest::testPintGitlabFormatReportsTheMessyFixtureAsCodeQualityJson`
- `PresetsTest::testPintGitlabFormatIsAnEmptyArrayForTheCleanFixture`

## `pint/laravel.json` [#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#pint-laravel-json)

Pint, Enforced.

- `PresetsTest::testPintLaravelPresetAcceptsTheLaravelAppFixture`

## `pint/per-strict.json` [#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#pint-per-strict-json)

Pint, Enforced.

- `PresetsTest::testPintPerStrictPresetAcceptsTheStrictTypesCleanFixture`
- `PresetsTest::testPintPerStrictPresetRejectsTheStrictTypesMissingFixture`

## `pint/laravel-strict.json` [#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#pint-laravel-strict-json)

Pint, Enforced.

- `PresetsTest::testPintLaravelStrictPresetRejectsTheStrictTypesMissingFixture`

## `phpstan/raw.neon` [#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#phpstan-raw-neon)

PHPStan, Enforced.

- `PresetsTest::testPhpStanRawConfigAcceptsTheCleanFixture`
- `PresetsTest::testPhpStanRawConfigReportsTheNullBug`

## `phpstan/laravel.neon` [#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#phpstan-laravel-neon)

PHPStan + Larastan, Enforced.

- `PresetsTest::testLaravelPresetLoadsLarastanThroughTheExtensionInstaller`
- `PresetsTest::testPhpStanLaravelConfigAcceptsTheLaravelAppFixture`
- `PresetsTest::testPhpStanLaravelConfigReportsTheUndefinedEloquentProperty`

## Larastan env() rule[#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#larastan-env-rule)

PHPStan + Larastan, Enforced.

- `PresetsTest::testLaravelPresetReportsAnEnvCallOutsideConfig`
- `PresetsTest::testLaravelPresetAcceptsAConfigCallOutsideConfig`

## PHPStan ignore comments[#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#phpstan-ignore-comments)

PHPStan, Enforced.

- `PresetsTest::testPhpStanRawConfigReportsAnIgnoreWithoutAComment`
- `PresetsTest::testPhpStanRawConfigReportsAnIgnoreThatMatchesNothing`
- `PresetsTest::testPhpStanRawConfigAcceptsAnIgnoreWithAComment`

## `rector/base.php` [#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#rector-base-php)

Rector, Enforced.

- `PresetsTest::testRectorPresetAcceptsTheCleanFixture`
- `PresetsTest::testRectorPresetReportsChangesOnTheOldFixture`

## `eslint/javascript.js` [#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#eslint-javascript-js)

ESLint, Enforced.

- `tests/js/presets.test.mjs` (`npm test`)

## `eslint/typescript.js` [#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#eslint-typescript-js)

ESLint, Enforced.

- `tests/js/presets.test.mjs` (`npm test`)

## `prettier/index.json` [#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#prettier-index-json)

Prettier, Enforced.

- `tests/js/prettier.test.mjs` (`npm test`)

## README "Tool floors"[#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#readme-tool-floors)

composer.lock + package-lock.json, Enforced.

- `ToolFloorsTest::testToolFloorsMatchTheLockFiles`

## Related[#](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html#related)

- [Tested with and tool floors](https://pinstripe.apps.wayne.edu/docs/reference/tested-with.html)
- [ADR-0001: PHPStan level 8 and baselines](https://pinstripe.apps.wayne.edu/docs/adr/0001-phpstan-level-and-baseline-policy.html)
- [Baselines only shrink](https://pinstripe.apps.wayne.edu/docs/guides/baselines.html)
- [Changelog](https://pinstripe.apps.wayne.edu/docs/changelog.html)
