---
title: Upgrading
description: How to upgrade Pinstripe, version by version.
---

# Upgrading

This page walks through each released version of Pinstripe, what changed for consumers, and how to verify the upgrade.

## What a stricter preset means to you

A stricter preset breaks consumers' builds. Pinstripe ships such a change in a minor version, not a major one, so you opt in by updating the package. When a version below marks a change as breaking, it is a stricter preset taking effect in your app the moment you update to it; hold the current version until you are ready.

## 0.3.2 (2026-11-10)

No breaking change: one preset is looser, none is stricter, so every cut that passed on 0.3.1 still passes.

**What changed for you:** the Vue presets let Inertia page components under `Pages/` or `pages/` have one-word names such as `Index` and `Show` (`vue/multi-word-component-names` is off there only, and still on everywhere else). An app that renamed or disabled the rule for its pages can undo that. Pinstripe now has a fixture for every stack app shape (Laravel plus Inertia plus Vue, raw PHP plus Vue), and the ban tables name the OWASP ASVS 5.0 requirement each ban enforces.

**Steps:** Your constraint is `^0.3`, which already allows 0.3.2: run `composer update waynestate/pinstripe`. Move `@waynestate/pinstripe` to 0.3.2 when `npm view @waynestate/pinstripe version` shows it (npm is published by hand after the tag).

**How to verify:** `composer show waynestate/pinstripe` reports 0.3.2; `npm run lint` on an Inertia app no longer flags a one-word component under `Pages/`, and still flags one elsewhere.

## 0.3.1 (2026-11-09)

No breaking change and no preset change: every cut passes and fails exactly what it did in 0.3.0.

**Also new for you:** [Banned calls](reference/banned-calls.md#sql-kept-in-sql-files) shows the one-helper fix for SQL kept in `.sql` files and says to type a wrapper's SQL parameter `literal-string` instead of baselining it; the README and [Raw PHP apps](guides/raw-php.md#show-phpstan-findings-in-the-merge-request) show PHPStan findings in the merge request (`analyse:gitlab`). If you copied a Code Quality snippet from an earlier version, add `when: always` under `artifacts:`: without it GitLab drops the report whenever the lint job fails.

**Steps:** Your constraint is `^0.3`, which already allows 0.3.1: run `composer update waynestate/pinstripe`. The ESLint and Prettier presets have not changed since 0.1.4, so move `@waynestate/pinstripe` to 0.3.1 whenever `npm view @waynestate/pinstripe version` shows it.

**How to verify:** `composer show waynestate/pinstripe` reports 0.3.1, and `composer analyse` reports exactly what it did on 0.3.0.

## 0.3.0 (2026-11-09)

**Breaking (a stricter fit):** every cut bans three more families of calls, all listed on [Banned calls](reference/banned-calls.md). SQL joined from strings (`pinstripe.banned.joinedSql`): a variable concatenated, interpolated or `sprintf()`'d into the SQL passed to `PDO::query()`, `prepare()` or `exec()`, mysqli's `query()`, `prepare()`, `mysqli_query()` and the rest, `oci_parse()` (and the old alias `ociparse()`), or Laravel's `DB::select()`, `insert()`, `update()`, `delete()`, `statement()`, `scalar()`, `cursor()` and `fromQuery()`. TLS checks turned off (`pinstripe.banned.curlVerifyOff`, `pinstripe.banned.streamVerifyOff` and `pinstripe.banned.verifyFalse`): `curl_setopt()` or `curl_setopt_array()` with `CURLOPT_SSL_VERIFYPEER` false or `CURLOPT_SSL_VERIFYHOST` 0, `stream_context_create()` with `ssl` `verify_peer` or `verify_peer_name` false or `allow_self_signed` true, and Guzzle's `verify` option set to false, through Laravel's `withOptions()`, `new GuzzleHttp\Client()` or a request's options. And in Laravel apps, Laravel's own `dd()` and `dump()` methods (`pinstripe.banned.dd` and `pinstripe.banned.dump`): `$users->dd()`, `DB::table('users')->dumpRawSql()`, `$request->dump()`, `Http::get($url)->dd()`, `Http::dump()` and the rest of the family, not only the functions. Your own methods named `dump()` and the schema dump behind `php artisan schema:dump` are not affected.

**Steps:** Your constraint is `^0.2`, which does not allow 0.3.0, so nothing changes until you move. When you are ready, run `composer require --dev waynestate/pinstripe:^0.3`. If Composer cannot find a 0.3 version yet (Packagist reads the GitHub mirror, which can trail a release), add the canonical repository first with `composer config repositories.pinstripe vcs https://git.wayne.edu/enterprise-apps/pinstripe.git`, as [Raw PHP apps](guides/raw-php.md#add-the-dependency) shows. Nothing changes on the npm side: the ESLint and Prettier presets are the same cut as 0.1.4, so move `@waynestate/pinstripe` to 0.3.0 whenever `npm view @waynestate/pinstripe version` shows it. Then delete each reported `dd()` or `dump()` leftover. For a TLS finding, turn verification back on, and if the server uses a private CA, point the client at its bundle (`CURLOPT_CAINFO`, the `ssl` `cafile` option, or `verify` set to the bundle's path). For joined SQL, put a `?` or `:name` placeholder where the variable was and pass the value as a binding (`oci_bind_by_name()` for oci8). SQL that is safe to join can be typed `literal-string`, and an older app with many findings can baseline the rest while they come out, see [Banned calls](reference/banned-calls.md). Keep one on purpose with `@phpstan-ignore pinstripe.banned.<name> (why)`.

**How to verify:** `composer analyse` passes, or lists only the calls you now own.

## 0.2.0 (2026-11-08)

**Breaking (a stricter fit):** every cut bans `eval()`, `extract()`, `var_dump()`, `dd()`, `dump()` and Laravel's `PendingRequest::withoutVerifying()` (including `Http::withoutVerifying()`), through `spaze/phpstan-disallowed-calls`, which Pinstripe now requires. An app with one of these left in fails `composer analyse` once it updates. Each finding's tip links to [Banned calls](reference/banned-calls.md) and to the [CREED rule](https://creed.apps.wayne.edu/handbook/code.html#banned-patterns) it enforces.

**Also new for you:** the Rector preset turns on the composer-based Laravel sets, so an app that installs `driftingly/rector-laravel` sees the Laravel upgrade rules for its installed Laravel version in a dry-run; apps without it see no change.

**Steps:** Your constraint is `^0.1`, which does not allow 0.2.0, so nothing changes until you move. When you are ready, run `composer require --dev waynestate/pinstripe:^0.2` (and `npm install --save-dev @waynestate/pinstripe@^0.2` once 0.2.0 is on npm; check with `npm view @waynestate/pinstripe version`). Composer pulls in `spaze/phpstan-disallowed-calls` and the extension installer registers it, so `phpstan.neon` needs no new line. Fix each reported call as the message says, or keep one on purpose with `@phpstan-ignore pinstripe.banned.<name> (why)`.

**How to verify:** `composer analyse` passes, or lists only the banned calls you now own, and `vendor/bin/rector process --dry-run` shows only changes you expect.

## 0.1.5 (2026-11-09)

For apps still on `^0.1`. No breaking change and no change to the presets: the package metadata and docs point at the canonical repository on git.wayne.edu and its GitHub mirror, so the entry Composer writes into your `composer.lock` names only public addresses.

**Steps:** Your constraint is `^0.1`, which already allows 0.1.5: run `composer update waynestate/pinstripe`.

**How to verify:** `composer show waynestate/pinstripe` reports 0.1.5, and the Pinstripe entry in your `composer.lock` names git.wayne.edu and github.com only.

## 0.1.4 (2026-11-06)

**Breaking (three tailoring changes, each a stricter fit):**

- `pinstripe-baseline` counts or checks a baseline only when the app's `composer.json` carries `"extra": { "pinstripe": { "legacy": true } }`. A baseline is for apps adopting Pinstripe late; new apps never set the flag.
- The ESLint presets (`@waynestate/pinstripe/eslint/javascript` and `/typescript`) fail lint on every Vue and accessibility finding: rules the plugins' recommended configs report as warnings are now errors, so the editor, a local `npm run lint` and CI agree. Apps that need the old behavior for a while spread `@waynestate/pinstripe/eslint/javascript-warn` or `/typescript-warn` instead.
- The Prettier preset formats Blade views with `prettier-plugin-blade` (pinned to 3.3.3). The first run rewrites every `*.blade.php` file (4-space indent, a space after directive names such as `@vite (`, void tags self-closed), so reformat in a commit of its own.

**Steps:** Your constraint is `^0.1`, which already allows 0.1.4, so update the locked version of `waynestate/pinstripe` to 0.1.4 (and `@waynestate/pinstripe` to 0.1.4 once it is on npm; check with `npm view @waynestate/pinstripe version`). If you use `pinstripe-baseline`, add the `legacy` flag first. Copy the two `.npmrc` lines from the README's "Supply chain" section into your app. To see ESLint findings in merge requests, run `eslint --format gitlab`.

**How to verify:** `composer analyse` and `npm run lint` pass (or list the findings you now own), `pinstripe-baseline check` still counts your baseline, and `npx prettier --check "resources/views/**/*.blade.php"` passes after the one-time reformat.

## 0.1.3 (2026-11-03)

No breaking change. The one change that touches your app is that the install instructions now include `composer config allow-plugins.phpstan/extension-installer true`, so a non-interactive install works. The remaining items (the ADR, the Node 22, 24 and 26 test matrix, the `laravel-vue` fixture, and the CI and deploy pins) are internal to Pinstripe.

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
