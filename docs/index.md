---
title: Overview
description: Pinstripe in one page: what it is, who it is for, and where to go next.
---

# Overview

This page summarizes what Pinstripe is, shows the install and run commands, and points you to the guide for your stack.

## What Pinstripe is

**Sharp code. Every line.**

The coding standard for the Wayne State app stack: shared presets for Pint, PHPStan (with Larastan for Laravel), Rector,
and ESLint with Prettier. Your app points at them and adds only its own exceptions. Pinstripe is to code what a good
suit is to a job interview: nobody notices it, everybody notices when it's missing.

It needs PHP ^8.3, runs PHPStan at level 8, and is MIT licensed. Checks: `composer ci` (the same command runs on GitLab and GitHub).

> [!NOTE]
>
> **Not to be confused with:** Pinstripe is the code style settings you run locally; Preflight runs them, and PROCTOR, in CI.
> Pinstripe's Rector set modernizes code within the app's current PHP version, while Booster upgrades the app to a newer
> PHP version.

## Install and run

In your app, add Pinstripe as a dev dependency, then run the checks:

<!-- docs-test: skip runs in the app, not in this repo -->

```bash
composer config allow-plugins.phpstan/extension-installer true
composer require --dev waynestate/pinstripe:^0.1
composer lint
composer fix
composer analyse
```

`composer lint` fails on style. `composer fix` fixes it. `composer analyse` runs PHPStan at level 8, with deprecation checks too.

## Pick your path

- [Raw PHP apps](guides/raw-php.md): point PHPStan at the raw config and Pint at the `per` preset.
- [Laravel apps](guides/laravel.md): the Laravel preset, with Larastan registered for you.
- [Vue](guides/vue.md): the ESLint and Prettier presets for JavaScript, TypeScript and Vue.
- [Rector](guides/rector.md): modernize code for the PHP version your app already supports.
- [Tested with](reference/tested-with.md): the tool versions Pinstripe is tested with, and the floors each tool needs.

## Status

The repository is at version 0.1.4. The npm package, `@waynestate/pinstripe`, follows each tag at the same version, published by hand. The canonical repository is on nougat; git.wayne.edu and GitHub are read-only mirrors of it. The agent-ready site ships a plain-text twin for machines: [llms.txt](../llms.txt).

## Related

- [Upgrading](upgrading.md)
- [Contributing](contributing.md)
- [Changelog](changelog.md)
