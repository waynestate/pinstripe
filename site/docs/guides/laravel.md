---
title: "Laravel apps | Pinstripe docs"
description: "Use the Pinstripe presets in a Laravel app."
url: https://pinstripe.apps.wayne.edu/docs/guides/laravel.html
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# Laravel apps[#](https://pinstripe.apps.wayne.edu/docs/guides/laravel.html#laravel-apps)

This guide shows how to point a Laravel app at Pinstripe's shared presets for Pint and PHPStan, with the Laravel parts included.

## Install[#](https://pinstripe.apps.wayne.edu/docs/guides/laravel.html#install)

Add Pinstripe and Larastan as dev dependencies:

```
composer config allow-plugins.phpstan/extension-installer true
composer require --dev waynestate/pinstripe:^0.1 larastan/larastan
```

Note

Pinstripe loads its PHPStan rules through `phpstan/extension-installer`, and Composer will not ask when it cannot prompt. Pinstripe pulls in `phpstan/extension-installer`, which registers Larastan for you. When Composer asks, allow the plugin (`config.allow-plugins` in your `composer.json`).

## Configure[#](https://pinstripe.apps.wayne.edu/docs/guides/laravel.html#configure)

Your `phpstan.neon` includes `vendor/waynestate/pinstripe/phpstan/laravel.neon` and nothing else: there is no Larastan line to add, and the scripts point at `vendor/waynestate/pinstripe/pint/laravel.json`.

## The env() rule[#](https://pinstripe.apps.wayne.edu/docs/guides/laravel.html#the-env-rule)

The Laravel preset also rejects `env()` outside `config/`: once the config is cached, `env()` returns null, so read config with `config()` instead. The rule is Larastan's, and `phpstan/laravel.neon` pins it on. The environment is not a hand-me-down: take the config out of the wardrobe.

## Proven on Laravel[#](https://pinstripe.apps.wayne.edu/docs/guides/laravel.html#proven-on-laravel)

`composer ci` lints a minimal Laravel 13 app (`tests/fixtures/laravel-app`) with `pint/laravel.json` and analyzes it with `phpstan/laravel.neon` on every run, and fails if the app stops passing. A smaller fixture (`tests/fixtures/laravel`) proves the preset really loads Larastan: it plants an undefined Eloquent property that only Larastan reports, and a clean twin it leaves alone. The combined fixture `tests/fixtures/laravel-vue` is one app tree with both PHP and Vue files, with `resources/js` beside `app/`. `composer ci` lints its PHP side with Pint and PHPStan and its Vue side with ESLint and Prettier on every run, with one planted PHP error and two planted Vue defects that each preset must report.

## Related[#](https://pinstripe.apps.wayne.edu/docs/guides/laravel.html#related)

- [Raw PHP apps](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html)
- [Require strict types](https://pinstripe.apps.wayne.edu/docs/guides/strict-types.html)
- [The strict cut](https://pinstripe.apps.wayne.edu/docs/guides/strict-cut.html)
- [Adopting on an existing app](https://pinstripe.apps.wayne.edu/docs/guides/adopting.html)
- [Best practices](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html)
