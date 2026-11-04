---
title: Upgrading
description: How to upgrade Pinstripe, version by version.
---

# Upgrading

This page walks through each released version of Pinstripe, what changed for consumers, and how to verify the upgrade.

## What a stricter preset means to you

A stricter preset breaks consumers' builds. Pinstripe ships such a change in a minor version, not a major one, so you opt in by updating the package. When a version below marks a change as breaking, it is a stricter preset taking effect in your app the moment you update to it; hold the current version until you are ready.

## Unreleased

Nothing is released yet. The one change that touches your app is that the install instructions now include `composer config allow-plugins.phpstan/extension-installer true`, so a non-interactive install works. The remaining items (the docs site, the ADR, the Node 22, 24 and 26 test matrix, the `laravel-vue` fixture, and the CI and deploy pins) are internal to Pinstripe.

## 0.1.2

**Breaking (Laravel apps):** `phpstan/laravel.neon` now pins Larastan's `noEnvCallsOutsideOfConfig` rule on, so an `env()` call outside `config/` fails analysis. This is a stricter preset shipped in a minor version, so you opt in by updating rather than being forced into a breaking major.

**Steps:** Your constraint is `^0.1`, which already allows 0.1.2, so update the locked version of `waynestate/pinstripe` to 0.1.2.

**How to verify:** Run `composer analyse` in your app. Any `env()` call outside `config/` is now reported; read config with `config()` instead, because once the config is cached `env()` returns null.

## 0.1.1

No breaking change: this release only removes reports the raw and Laravel cuts should never have made. It fixes the raw and Laravel PHPStan cuts inheriting `reportMaybesInMethodSignatures` and `reportStaticMethodSignatures` from `phpstan/phpstan-strict-rules`, which made them report phpdoc method signature issues they should not; `base.neon` now pins both to false and `strict.neon` keeps them on. It also pins the other eight parameters `phpstan/phpstan-strict-rules` forces on: four low-noise ones stay on in every cut, and `checkDynamicProperties`, `reportMaybesInPropertyPhpDocTypes`, `reportWrongPhpDocTypeInVarTag` and `reportNonIntStringArrayKey` are off in `base.neon` and on in `strict.neon`.

**Steps:** Your constraint is `^0.1`, which already allows 0.1.1, so update the locked version of `waynestate/pinstripe` to 0.1.1.

**How to verify:** Run `composer analyse` in your app. The raw and Laravel cuts no longer report the inherited phpdoc method signature issues.

## 0.1.0

First release, so there is nothing to break. It adds `bin/pinstripe-baseline`, which counts a PHPStan baseline's ignored errors (`count`) and fails when the baseline grows (`check --max=<n>` or `check --against=<git-ref>`), so baselines only shrink. It adds `phpstan/strict.neon`, an opt-in PHPStan preset for new apps that turns on `phpstan/phpstan-strict-rules`, and adds `tests/fixtures/per`, a Pint fixture proving the `per` preset is the PER Coding Style.

**Steps:** Add Pinstripe as a dev dependency with `composer require --dev waynestate/pinstripe:^0.1`, then wire the presets into your app.

**How to verify:** In your app, run `composer lint` and `composer analyse` once the presets are wired in.

## Related

- [Changelog](changelog.md)
- [Adopting on an existing app](guides/adopting.md)
- [Baselines only shrink](guides/baselines.md)
- [The strict cut](guides/strict-cut.md)
- [Contributing](contributing.md)
