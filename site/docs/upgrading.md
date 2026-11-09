---
title: "Upgrading | Pinstripe docs"
description: "How to upgrade Pinstripe, version by version."
url: https://pinstripe.apps.wayne.edu/docs/upgrading.html
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# Upgrading[#](https://pinstripe.apps.wayne.edu/docs/upgrading.html#upgrading)

This page walks through each released version of Pinstripe, what changed for consumers, and how to verify the upgrade.

## What a stricter preset means to you[#](https://pinstripe.apps.wayne.edu/docs/upgrading.html#what-a-stricter-preset-means-to-you)

A stricter preset breaks consumers' builds. Pinstripe ships such a change in a minor version, not a major one, so you opt in by updating the package. When a version below marks a change as breaking, it is a stricter preset taking effect in your app the moment you update to it; hold the current version until you are ready.

## 0.1.5 (2026-11-09)[#](https://pinstripe.apps.wayne.edu/docs/upgrading.html#0-1-5-2026-11-09)

No breaking change and no change to the presets. The package metadata (`support` in `composer.json`, `repository` and `bugs` in `package.json`) and the docs now point at the canonical repository on git.wayne.edu and its GitHub mirror, so the entry Composer writes into your `composer.lock` names only public addresses.

**Steps:** Your constraint is `^0.1`, which already allows 0.1.5: run `composer update waynestate/pinstripe`.

**How to verify:** `composer show waynestate/pinstripe` reports 0.1.5, and the Pinstripe entry in your `composer.lock` names git.wayne.edu and github.com only.

## 0.1.4 (2026-11-06)[#](https://pinstripe.apps.wayne.edu/docs/upgrading.html#0-1-4-2026-11-06)

**Breaking (three tailoring changes, each a stricter fit):**

- `pinstripe-baseline` counts or checks a baseline only when the app's `composer.json` carries `"extra": { "pinstripe": { "legacy": true } }`. A baseline is for apps adopting Pinstripe late; new apps never set the flag.
- The ESLint presets (`@waynestate/pinstripe/eslint/javascript` and `/typescript`) fail lint on every Vue and accessibility finding: rules the plugins' recommended configs report as warnings are now errors, so the editor, a local `npm run lint` and CI agree. Apps that need the old behavior for a while spread `@waynestate/pinstripe/eslint/javascript-warn` or `/typescript-warn` instead.
- The Prettier preset formats Blade views with `prettier-plugin-blade` (pinned to 3.3.3). The first run rewrites every `*.blade.php` file (4-space indent, a space after directive names such as `@vite (`, void tags self-closed), so reformat in a commit of its own.

**Steps:** Your constraint is `^0.1`, which already allows 0.1.4, so update the locked version of `waynestate/pinstripe` to 0.1.4 (and `@waynestate/pinstripe` to 0.1.4 once it is on npm; check with `npm view @waynestate/pinstripe version`). If you use `pinstripe-baseline`, add the `legacy` flag first. Copy the two `.npmrc` lines from the README's "Supply chain" section into your app. To see ESLint findings in merge requests, run `eslint --format gitlab`.

**How to verify:** `composer analyse` and `npm run lint` pass (or list the findings you now own), `pinstripe-baseline check` still counts your baseline, and `npx prettier --check "resources/views/**/*.blade.php"` passes after the one-time reformat.

## 0.1.3 (2026-11-03)[#](https://pinstripe.apps.wayne.edu/docs/upgrading.html#0-1-3-2026-11-03)

No breaking change. The one change that touches your app is that the install instructions now include `composer config allow-plugins.phpstan/extension-installer true`, so a non-interactive install works. The remaining items (the docs site, the ADR, the Node 22, 24 and 26 test matrix, the `laravel-vue` fixture, and the CI and deploy pins) are internal to Pinstripe.

## 0.1.2[#](https://pinstripe.apps.wayne.edu/docs/upgrading.html#0-1-2)

**Breaking (Laravel apps):** `phpstan/laravel.neon` now pins Larastan's `noEnvCallsOutsideOfConfig` rule on, so an `env()` call outside `config/` fails analysis. This is a stricter preset shipped in a minor version, so you opt in by updating rather than being forced into a breaking major.

**Steps:** Your constraint is `^0.1`, which already allows 0.1.2, so update the locked version of `waynestate/pinstripe` to 0.1.2.

**How to verify:** Run `composer analyse` in your app. Any `env()` call outside `config/` is now reported; read config with `config()` instead, because once the config is cached `env()` returns null.

## 0.1.1[#](https://pinstripe.apps.wayne.edu/docs/upgrading.html#0-1-1)

No breaking change: this release only removes reports the raw and Laravel cuts should never have made. It fixes the raw and Laravel PHPStan cuts inheriting `reportMaybesInMethodSignatures` and `reportStaticMethodSignatures` from `phpstan/phpstan-strict-rules`, which made them report phpdoc method signature issues they should not; `base.neon` now pins both to false and `strict.neon` keeps them on. It also pins the other eight parameters `phpstan/phpstan-strict-rules` forces on: four low-noise ones stay on in every cut, and `checkDynamicProperties`, `reportMaybesInPropertyPhpDocTypes`, `reportWrongPhpDocTypeInVarTag` and `reportNonIntStringArrayKey` are off in `base.neon` and on in `strict.neon`.

**Steps:** Your constraint is `^0.1`, which already allows 0.1.1, so update the locked version of `waynestate/pinstripe` to 0.1.1.

**How to verify:** Run `composer analyse` in your app. The raw and Laravel cuts no longer report the inherited phpdoc method signature issues.

## 0.1.0[#](https://pinstripe.apps.wayne.edu/docs/upgrading.html#0-1-0)

First release, so there is nothing to break. It adds `bin/pinstripe-baseline`, which counts a PHPStan baseline's ignored errors (`count`) and fails when the baseline grows (`check --max=<n>` or `check --against=<git-ref>`), so baselines only shrink. It adds `phpstan/strict.neon`, an opt-in PHPStan preset for new apps that turns on `phpstan/phpstan-strict-rules`, and adds `tests/fixtures/per`, a Pint fixture proving the `per` preset is the PER Coding Style.

**Steps:** Add Pinstripe as a dev dependency with `composer require --dev waynestate/pinstripe:^0.1`, then wire the presets into your app.

**How to verify:** In your app, run `composer lint` and `composer analyse` once the presets are wired in.

## Related[#](https://pinstripe.apps.wayne.edu/docs/upgrading.html#related)

- [Changelog](https://pinstripe.apps.wayne.edu/docs/changelog.html)
- [Adopting on an existing app](https://pinstripe.apps.wayne.edu/docs/guides/adopting.html)
- [Baselines only shrink](https://pinstripe.apps.wayne.edu/docs/guides/baselines.html)
- [The strict cut](https://pinstripe.apps.wayne.edu/docs/guides/strict-cut.html)
- [Contributing](https://pinstripe.apps.wayne.edu/docs/contributing.html)
