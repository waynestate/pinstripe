---
title: "Overview | Pinstripe docs"
description: "Pinstripe in one page: what it is, who it is for, and where to go next."
url: https://pinstripe.apps.wayne.edu/docs/
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# Overview [#](https://pinstripe.apps.wayne.edu/docs/#overview)

This page summarizes what Pinstripe is, shows the install and run commands, and points you to the guide for your stack.

## What Pinstripe is [#](https://pinstripe.apps.wayne.edu/docs/#what-pinstripe-is)

**Sharp code. Every line.**

The coding standard for the Wayne State app stack: shared presets for Pint, PHPStan (with Larastan for Laravel), Rector, and ESLint with Prettier. Your app points at them and adds only its own exceptions. Pinstripe is to code what a good suit is to a job interview: nobody notices it, everybody notices when it's missing.

It needs PHP ^8.3, runs PHPStan at level 8, and is MIT licensed. Checks: `composer ci` (the same command runs on GitLab and GitHub).

Note

**Not to be confused with:** Pinstripe is the code style settings you run locally; Preflight runs them, and PROCTOR, in CI. Pinstripe's Rector set modernizes code within the app's current PHP version, while Booster upgrades the app to a newer PHP version.

## Install and run [#](https://pinstripe.apps.wayne.edu/docs/#install-and-run)

In your app, add Pinstripe as a dev dependency, then run the checks:

```bash
composer config allow-plugins.phpstan/extension-installer true
composer require --dev waynestate/pinstripe:^0.4
composer lint
composer fix
composer analyse
```

`composer lint` fails on style. `composer fix` fixes it. `composer analyse` runs PHPStan at level 8, with deprecation checks too.

## Pick your path [#](https://pinstripe.apps.wayne.edu/docs/#pick-your-path)

- [Raw PHP apps](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html): point PHPStan at the raw config and Pint at the `per` preset.
- [Laravel apps](https://pinstripe.apps.wayne.edu/docs/guides/laravel.html): the Laravel preset, with Larastan registered for you.
- [Vue](https://pinstripe.apps.wayne.edu/docs/guides/vue.html): the ESLint and Prettier presets for JavaScript, TypeScript and Vue.
- [Rector](https://pinstripe.apps.wayne.edu/docs/guides/rector.html): modernize code for the PHP version your app already supports.
- [Tested with](https://pinstripe.apps.wayne.edu/docs/reference/tested-with.html): the tool versions Pinstripe is tested with, and the floors each tool needs.

## Status [#](https://pinstripe.apps.wayne.edu/docs/#status)

The repository is at version 0.4.0: every cut bans SQL joined from strings, TLS checks turned off and leftover debug calls ([Banned calls](https://pinstripe.apps.wayne.edu/docs/reference/banned-calls.html)), and `^0.4` installs it. Packagist reads the GitHub mirror, which can trail a release; the [Raw PHP guide](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html#add-the-dependency) shows the one line that installs it from git.wayne.edu meanwhile. The npm package, `@waynestate/pinstripe`, follows each tag at the same version, published by hand; its ESLint and Prettier presets have not changed since 0.1.4. The canonical repository is on git.wayne.edu; GitHub is a read-only push mirror of it, and Packagist and npm read from it. The agent-ready site ships a plain-text twin for machines: [llms.txt](https://pinstripe.apps.wayne.edu/llms.txt).

## Related [#](https://pinstripe.apps.wayne.edu/docs/#related)

- [Upgrading](https://pinstripe.apps.wayne.edu/docs/upgrading.html)
- [Contributing](https://pinstripe.apps.wayne.edu/docs/contributing.html)
- [Changelog](https://pinstripe.apps.wayne.edu/docs/changelog.html)
