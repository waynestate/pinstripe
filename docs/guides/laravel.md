---
title: Laravel apps
description: Use the Pinstripe presets in a Laravel app.
---

# Laravel apps

This guide shows how to point a Laravel app at Pinstripe's shared presets for Pint and PHPStan, with the Laravel parts included.

## Install

Add Pinstripe and Larastan as dev dependencies:

```
composer config allow-plugins.phpstan/extension-installer true
composer require --dev waynestate/pinstripe:^0.1 larastan/larastan
```

> [!NOTE]
> Pinstripe loads its PHPStan rules through `phpstan/extension-installer`, and Composer will not ask when it cannot prompt. Pinstripe pulls in `phpstan/extension-installer`, which registers Larastan for you. When Composer asks, allow the plugin (`config.allow-plugins` in your `composer.json`).

## Configure

Your `phpstan.neon` includes `vendor/waynestate/pinstripe/phpstan/laravel.neon` and nothing else: there is no Larastan line to add, and the scripts point at `vendor/waynestate/pinstripe/pint/laravel.json`.

## The env() rule

The Laravel preset also rejects `env()` outside `config/`: once the config is cached, `env()` returns null, so read config with `config()` instead. The rule is Larastan's, and `phpstan/laravel.neon` pins it on. The environment is not a hand-me-down: take the config out of the wardrobe.

## Proven on Laravel

`composer ci` lints a minimal Laravel 13 app (`tests/fixtures/laravel-app`) with `pint/laravel.json` and analyzes it with `phpstan/laravel.neon` on every run, and fails if the app stops passing. A smaller fixture (`tests/fixtures/laravel`) proves the preset really loads Larastan: it plants an undefined Eloquent property that only Larastan reports, and a clean twin it leaves alone. The combined fixture `tests/fixtures/laravel-vue` is one app tree with both PHP and Vue files, with `resources/js` beside `app/`. `composer ci` lints its PHP side with Pint and PHPStan and its Vue side with ESLint and Prettier on every run, with one planted PHP error and two planted Vue defects that each preset must report.

## Related

- [Raw PHP apps](raw-php.md)
- [Require strict types](strict-types.md)
- [The strict cut](strict-cut.md)
- [Adopting on an existing app](adopting.md)
- [Best practices](../reference/best-practices.md)
