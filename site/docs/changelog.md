---
title: "Changelog | Pinstripe docs"
description: "All notable changes to this project."
url: https://pinstripe.apps.wayne.edu/docs/changelog.html
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# Changelog[#](https://pinstripe.apps.wayne.edu/docs/changelog.html#changelog)

All notable changes to Pinstripe are documented here.

## Unreleased[#](https://pinstripe.apps.wayne.edu/docs/changelog.html#unreleased)

- Added: every cut bans SQL joined from strings (`pinstripe.banned.joinedSql`, OWASP A05:2025): a variable concatenated, interpolated or `sprintf()`'d into the SQL given to `PDO::query()`, `prepare()` or `exec()`, mysqli's `query()`, `prepare()`, `mysqli_query()` and the rest, `oci_parse()` (and the old alias `ociparse()`), or Laravel's `DB::select()`, `insert()`, `update()`, `delete()`, `statement()`, `scalar()`, `cursor()` and `fromQuery()`. No taint rule: spaze/phpstan-disallowed-calls asks for PHPStan's own `literal-string` type, so literal SQL with bindings, class constants and literal pieces joined together pass; Laravel already types `DB::raw()`, `DB::unprepared()` and the `*Raw()` builder methods that way, and a test now holds it to that. Breaking for apps that build SQL from variables. New fixtures `JoinedSql.php`, `BoundSql.php`, `JoinedQueries.php` and `BoundQueries.php`.
- Docs: the Best practices docs page carries BEST_PRACTICES.md word for word again (it had fallen behind: no ESLint GitLab output row, no banned-call rows), and BEST_PRACTICES.md gains rows for `phpstan/strict.neon` and `phpstan/disallowed.neon`. New `tests/BestPracticesTest.php` keeps them in step: every test and file the table names exists, each row has one known status, an Enforced row names its proof, and the docs page matches.
- Fixed: the `Http::withoutVerifying()` ban (the static entry) carries the same tip as the others, linking to the Banned calls page and the CREED rule; `BannedCallLinksTest::testEveryBanCarriesAnIdentifierAndATip` now reads `phpstan/disallowed.neon` and fails on any entry without a message, identifier or tip.
- Added: every cut bans TLS checks turned off: `curl_setopt()` and `curl_setopt_array()` with `CURLOPT_SSL_VERIFYPEER` false or `CURLOPT_SSL_VERIFYHOST` 0 (`pinstripe.banned.curlVerifyOff`), `stream_context_create()` with `ssl` `verify_peer` or `verify_peer_name` false or `allow_self_signed` true (`pinstripe.banned.streamVerifyOff`), and Guzzle's `verify` option set to false through Laravel's `withOptions()`, `new GuzzleHttp\Client()` or a request's options (`pinstripe.banned.verifyFalse`), each tagged OWASP A02:2025. The `curl_setopt()` ban is Pinstripe's first PHPStan rule of its own (`src/PHPStan/CurlVerifyOffRule.php`), since it needs the option and the value together; the rest are spaze/phpstan-disallowed-calls entries. Breaking for apps that skip the certificate check. New fixtures `InsecureTransport.php`, `SecureTransport.php`, `VerifyFalse.php`, `VerifiedHttp.php` and the Laravel app's `banned/InsecureCurl.php`.
- Added: Laravel's own `dd()` and `dump()` methods are banned too (`$users->dd()`, `DB::table('users')->dumpRawSql()`, `$request->dump()`, `Http::get($url)->dd()`, `Http::dump()` and the rest of the family), under the functions' identifiers `pinstripe.banned.dd` and `pinstripe.banned.dump`; only methods Laravel declares match, so an app's own `dump()` and the schema dump behind `php artisan schema:dump` pass. Breaking for Laravel apps with one left in. New fixtures `tests/fixtures/laravel/src/LeftoverDebugMethods.php` and `SchemaDump.php`.
- Changed: CI pins the Preflight components to 0.4.0 (the proctor job clones the full history, so the override-approver check can read git blame).

## 0.2.0 (2026-11-08)[#](https://pinstripe.apps.wayne.edu/docs/changelog.html#0-2-0-2026-11-08)

- Changed: every banned call's tip links to the CREED rule it enforces (code handbook, Banned patterns) as well as to Pinstripe's banned-calls page; `tests/BannedCallLinksTest.php` keeps it that way.
- Docs: the README says how CREED's PHP floor reaches the tools (PROCTOR's php-floor check, then Booster; the presets keep reading composer.json); "Coming next" is empty for now, since both items it named are built.
- Changed: the deploy layer runs Squire's kit 0.7.1 (was 0.6.0): `.squire/php.ini` no longer sets the deprecated `session.sid_*` lines, web requests get a `disable_functions` list and Apache hardening (`.squire/php-web.ini`, `.squire/apache-hardening.conf`), and opcache gets production settings with `SQUIRE_OPCACHE_VALIDATE` as the one switch.
- Added: CI runs Preflight's a11y component (0.3.1) on site/, every page in light and dark at 320 and 1280 px with the WCAG 1.4.10 reflow check, blocking.
- Changed: CI pins the Preflight components to 0.3.1 (fixed cache keys, and caches saved even when a job fails; D-072).
- Changed: CI saves its Composer and npm caches even when a job fails, so a retry after a dropped download fetches only what is missing (D-072).
- Changed: CI caches Composer and npm downloads under a fixed key, so a lock change downloads only what changed (D-072).
- Changed: HELMET moves to `^1.0`, its first stable release, wherever this repo requires it.
- Fixed: the docs menu closes with Escape and hands focus back to its button (docs kit synced from Squire).
- Fixed: the docs kit's copy button no longer covers the first line of code when it says Copied or Failed: over the code it keeps its resting width and shows a check or a cross when the word does not fit (docs kit synced from Squire).
- Fixed: the front page's version chip shows the full version the docs show (size 0.1.4, was size 0.1). It carries `data-docs-version` now, so the docs builder (synced from Squire) rewrites it at every release and `composer docs:check` fails while it is stale.
- Changed: the deploy layer requires PROCTOR ^0.8 (locked 0.8.0).
- Changed: the pipeline pins every Preflight CI component to 0.3.0 ([preflight@0.3.0](mailto:preflight@0.3.0)); component image inputs are plain image names, since Preflight adds the Dependency Proxy prefix itself.
- Added: a test that proves `npm install --ignore-scripts` stops a postinstall script, and the eslint-config-prettier floor is ^10.1.8, past the releases hit by CVE-2025-54313.
- Added: banned calls. Every PHPStan cut includes the new `phpstan/disallowed.neon`, which bans `eval()`, `extract()`, `var_dump()`, `dd()`, `dump()` and Laravel's `PendingRequest::withoutVerifying()` (the `Http::withoutVerifying()` facade call too) through `spaze/phpstan-disallowed-calls` `^4.15` (now in `require`, locked 4.15.0, loaded by the extension installer). Each message says why and what to use instead, ends with its OWASP Top 10:2025 tag (A05 Injection or A02 Security Misconfiguration), and has a stable identifier (`pinstripe.banned.*`) for a reasoned ignore. Breaking for apps with one left in: a leftover `dd()` now fails the pipeline. `md5()`, `sha1()`, `rand()` and `mt_rand()` are not banned; that call is open. New README section and docs page, Banned calls; new fixtures `tests/fixtures/raw-disallowed`, `tests/fixtures/laravel/src/InsecureHttp.php` and `tests/fixtures/laravel-app/banned`.
- Added: the Rector preset turns on the composer-based Laravel sets (`withComposerBased(laravel: true)`); apps that install driftingly/rector-laravel get the Laravel upgrade rules for their installed version, and apps without it see no change.
- Changed: the front page's `deploy/build.sh` revision marker is 2, for a Citadel build-cache test (does a full Update run the edited `build.sh`?).
- Fixed: the pipeline's PROCTOR jobs no longer pass `--policy`, which PROCTOR 0.7 refuses in CI; PROCTOR checks against the CREED policy it bundles.
- Changed: the front page's deploy layer (`deploy/`) runs Squire's kit 0.6.0 (was 0.5.0): `scripts/release.sh` asks PROCTOR's deploy gate for a go or no-go on each release (a report, not a stop, unless `SQUIRE_PROCTOR=enforce`), and `.env.squire.example` lists the new setting. The layer's pins move to HELMET `^0.4` (locked 0.4.0) and PROCTOR `^0.7` (locked 0.7.0).
- Fixed: the front page says Sourdough starts all five app shapes and that every app it makes comes with Pinstripe on; it said raw PHP only, and that Sourdough apps did not include Pinstripe yet.
- Changed: CI caches Composer and npm downloads per lock file (`.composer-cache`, keyed on `composer.lock` and `deploy/composer.lock`, and `.npm-cache`), so a pipeline stops fetching every package from Packagist and npm on every run. Images already come through the Dependency Proxy.
- Changed: the front page's deploy layer (`deploy/`) runs Squire's kit 0.5.0 (was 0.3.4): `oracle-check.php` arrives, `scripts/release.sh`, `scripts/parity.ps1` and `.squire/base-fallback.Dockerfile` take the new templates, and `docker-compose.squire.yml`, which Renovate had edited, is Squire's template again, so `squire init --check` passes. `site/assets/stack-env.js` takes the stack's one copy, with bindingless catch blocks.
- Changed: The canonical repository is git.wayne.edu (ADR-0021); docs, links and package metadata no longer point at the archived nougat copy.
- Added: The Proof page records its last run.

## 0.1.4 (2026-11-06)[#](https://pinstripe.apps.wayne.edu/docs/changelog.html#0-1-4-2026-11-06)

- Fixed: `pinstripe-baseline` mutes deprecations while it loads the app's autoloader, so an old package in the app no longer prints PHP warnings ahead of the count a script reads (found by the 2026-11-05 proof run; new test in tests/BaselineTest.php).
- Added: a public Proof page (`docs/reference/proof.md`, under Reference): the presets run against 82 Wayne State applications, what held, what the run caught in Pinstripe, and what it does not prove.
- Docs: the install steps say to rerun `composer require` with `-W` when a package the app already locks blocks it; Adopting on an existing app covers errors PHPStan cannot baseline and keeping Pint off bundled third-party code.
- Security: the front-page deploy layer moves to Symfony 7.4.23 (symfony/http-foundation and symfony/yaml advisories, including CVE-2026-118514).
- Changed: the docs, README and front page say the npm package, `@waynestate/pinstripe`, is at 0.1.3 (published); the docs pages' dark mode is now charcoal suiting instead of the green cloth, and wide tables shade the edge they scroll toward.
- Changed: the docs pages wear the front page's look: its header and main menu (Docs marked current), Bodoni titles with the gold chalk line, pattern-piece callouts and pager, the gold Copy pill and the green footer, plus a dark mode cut from the green cloth. The theme lives in `docs/_header.html` and `docs/theme/pinstripe.css`, named by the `theme` key in `docs/docs.json`; the vendored docs builder and kit are synced from Squire's unified builder (theme hook, callouts, copy button and keyboard fixes).
- Added: `min-release-age=3` to the repo `.npmrc` beside the existing `ignore-scripts=true`, with a comment naming the unit (days) and the agreeing Renovate `minimumReleaseAge` rule, so npm waits 3 days before installing a freshly published release and never runs dependency lifecycle scripts; the README gains a "Supply chain" section that explains both settings and tells apps to copy both lines into their own `.npmrc`.
- Changed: `pinstripe-baseline` now requires the app's composer.json to carry `"extra": { "pinstripe": { "legacy": true } }` before it counts or checks a baseline; this is breaking for existing baseline users, who must add the flag, because a baseline is only for apps adopting Pinstripe late and new apps never set it.
- Added: `--composer=<path>` to `pinstripe-baseline count` and `check` (default `composer.json` in the current working directory), so tests and monorepos can point at another composer.json.
- Changed: the ESLint presets (`eslint/javascript.js` and `eslint/typescript.js`) fail lint on every Vue and accessibility finding: rules the plugins' recommended configs report as warnings are raised to errors, so the editor, a local `npm run lint` and CI agree on what fails. Pre-1.0 break: apps that keep the old behavior (those findings as warnings) spread the new `-warn` export instead.
- Added: `eslint/javascript-warn.js` and `eslint/typescript-warn.js`, exported as `@waynestate/pinstripe/eslint/javascript-warn` and `@waynestate/pinstripe/eslint/typescript-warn`, which keep the exact previous behavior (the plugins' plain recommended configs, warnings stay warnings) for apps adopting the stricter presets gradually; `tests/js/presets.test.mjs` gains four tests around a `tests/js/warn-tier` fixture whose only findings (`vue/attributes-order`, `vue/no-v-html`) were warnings before, proving the strict presets report them as errors and the `-warn` presets keep them as warnings.
- Added: a docs site (site/docs/) built by the shared docs builder from docs/, checked in composer ci.
- Added: `eslint-formatter-gitlab` as a dependency and `tests/js/gitlab-formatter.test.mjs`, which lints the bad Vue fixture with the javascript preset, runs the results through ESLint's `gitlab` formatter, and asserts the Code Quality report is a non-empty JSON array whose findings each carry a description, fingerprint, severity and location, so `eslint --format gitlab` shows ESLint findings in GitLab merge requests; the README gains a "Show ESLint findings in the merge request" note for the Vue section.
- Added: `prettier-plugin-blade` (pinned to 3.3.3, no caret) as a dependency and a `*.blade.php` override in `prettier/index.json` (parser `blade`, 4-space tab width), so the Prettier preset formats Blade views; `tests/js/blade.test.mjs` checks a clean and a messy Blade fixture through Prettier's Node API, and the README gains a "Format Blade views" section with the one-time reformat command and an adopting note.
- Changed: Blade files now format with prettier-plugin-blade; the first run rewrites every Blade file (4-space indent, a space after directive names such as `@vite (`, void tags self-closed), so reformat in its own commit.

## 0.1.3 (2026-11-03)[#](https://pinstripe.apps.wayne.edu/docs/changelog.html#0-1-3-2026-11-03)

- Added: `docs/adr/0001-phpstan-level-and-baseline-policy.md`, the ADR recording the PHPStan level 8 cut, the baseline policy (generated once for an existing app, never for new code, and only ever shrunk), and the `composer ci` / `composer-ci` pipeline enforcement point, plus a README note that apps adopting Oxlint keep ESLint for `.vue` files because the Vue and accessibility plugins run only in ESLint.
- Changed: the install instructions now include `composer config allow-plugins.phpstan/extension-installer true`, so a non-interactive install works.
- Added: a Node matrix to the `npm-test` jobs on GitLab and GitHub (Node 22, 24 and 26), so the ESLint and Prettier presets are proven on all three majors; the floor stays 22.13.
- Changed: the front-page deploy layer pins HELMET ^0.3, PROCTOR ^0.4 and CREED ^0.1 (lock: HELMET 0.3.0, PROCTOR 0.4.0, CREED 0.1.6).
- Changed: CI pins Preflight 0.1.4 (GitLab component includes and the GitHub reusable workflow tag).
- Added: `tests/fixtures/laravel-vue`, a combined Laravel + Vue fixture with `resources/js` beside `app/`, so one app tree is linted by Pint and PHPStan (PHP side) and ESLint and Prettier (Vue side) on every run; it carries one planted PHP error (`bad/BrokenGreeting.php`) and two planted Vue defects (`resources/js/bad/Broken.vue` missing a `v-for` key, `resources/js/bad/NoAlt.vue` missing an `alt`), and `tests/js/presets.test.mjs` gains five tests for the Vue side.

## 0.1.2 (2026-11-03)[#](https://pinstripe.apps.wayne.edu/docs/changelog.html#0-1-2-2026-11-03)

- Added: PHPUnit tests for the PHPStan ignore rules in `phpstan/base.neon`, which run the raw preset against three one-class fixtures in `tests/fixtures/raw-ignores` and prove an `@phpstan-ignore` without a comment fails, an ignore that matches no error fails, and a commented ignore passes.
- Added: PHPUnit tests for Pint's `--format=gitlab` output, which prove the messy `per` fixture becomes a non-empty GitLab Code Quality JSON array and the clean fixture becomes `[]`.
- Added: a "Tool floors" section in the README with the PHP or Node floor of each locked tool, read verbatim from `composer.lock` and `package-lock.json` and checked by `tests/ToolFloorsTest.php`, so a dependency bump fails `composer ci` until the table is re-cut.
- Added: `phpstan/laravel.neon` pins Larastan's `noEnvCallsOutsideOfConfig` rule on, with `tests/fixtures/laravel/src/EnvLeak.php` and `tests/fixtures/laravel/src/ConfigRead.php` proving the Laravel preset reports an `env()` call outside `config/` and accepts a `config()` call there.
- Added: PHPUnit tests for the opt-in strict-types Pint presets, which run the real Pint against clean and missing fixtures for `pint/per-strict.json` and `pint/laravel-strict.json` and assert each strict config declares its preset and the `declare_strict_types` rule.
- Added: `tests/js/prettier.test.mjs`, which runs the Prettier preset through Prettier's Node API on clean and messy JS and Vue fixtures and on the preset's own options, so `npm test` enforces the preset like the ESLint ones.
- Added: PHPUnit tests for `pinstripe-baseline check --against=<git-ref>`, which build a throwaway git repository in a temp dir and assert the count at the ref and the exit codes.
- Added: `npm-test` CI jobs on GitLab and GitHub that run `npm ci` then `npm test` on Node 22, so the ESLint and Prettier presets are covered in CI.
- Changed: the GitHub workflow pins Preflight's reusable workflow by tag (0.1.2) instead of a SHA, as its comment intended.
- Added: agent-ready site (prompt blocks, Markdown twins, llms.txt), from Squire's kit.
- Pinned symfony/mime v7.4.22 in composer.lock, the fixed version for CVE-2026-45070 (PKSA-wtxr-p26d-nn42, GHSA-vqc8-7275-q272), so the audit step of `composer ci` passes.
- Changed: project names written as words except acronyms.
- Changed: the deploy layer's Squire kit is refreshed to 0.3.3 (release options can now live in ./.env.squire).
- Changed: the deploy layer's Squire kit is refreshed to 0.3.4 (the local .env.squire file is now git-ignored).
- Fixed: stale capability claims in the site and README.
- Added: `tests/PackageVersionTest.php`, which fails `composer ci` when the `version` in `package.json` does not match the newest `## X.Y.Z (date)` heading in `CHANGELOG.md` or, on a tag pipeline, the tag itself, so npm can always publish from the tag; `package.json` is now 0.1.1, the version the 0.1.1 tag shipped without.
- Changed: CI pulls its Docker Hub images through the GitLab Dependency Proxy (the group's CI_DEPENDENCY_PROXY_GROUP_IMAGE_PREFIX), so pipelines no longer hit Docker Hub's anonymous pull limit.
- Changed: `deploy/composer.json` drops `minimum-stability: dev` (every constraint resolves at stable), and the PROCTOR job for `deploy/` in `.gitlab-ci.yml` drops `allow_failure`, since PROCTOR now passes on `deploy/`.
- Changed: docs and package metadata name nougat as the canonical repository, with git.wayne.edu and GitHub as read-only mirrors, until git.wayne.edu has CI runners.

## 0.1.1 (2026-10-29)[#](https://pinstripe.apps.wayne.edu/docs/changelog.html#0-1-1-2026-10-29)

- Fixed the raw and Laravel PHPStan cuts inheriting `reportMaybesInMethodSignatures` and `reportStaticMethodSignatures` from `phpstan/phpstan-strict-rules`, which made them report phpdoc method signature issues they should not; `base.neon` now pins both to false and `strict.neon` keeps them on.
- Pinned the other eight parameters `phpstan/phpstan-strict-rules` forces on: four low-noise ones stay on in every cut, and `checkDynamicProperties`, `reportMaybesInPropertyPhpDocTypes`, `reportWrongPhpDocTypeInVarTag` and `reportNonIntStringArrayKey` are off in `base.neon` and on in `strict.neon`; the README lists each decision.

## 0.1.0 (2026-10-29)[#](https://pinstripe.apps.wayne.edu/docs/changelog.html#0-1-0-2026-10-29)

- Added `bin/pinstripe-baseline`, which counts a PHPStan baseline's ignored errors (`count`) and fails when the baseline grows (`check --max=<n>` or `check --against=<git-ref>`), so baselines only shrink.
- Added `phpstan/strict.neon`, an opt-in PHPStan preset for new apps that turns on `phpstan/phpstan-strict-rules`.
- Added `tests/fixtures/per`, a Pint fixture proving the `per` preset is the PER Coding Style.
