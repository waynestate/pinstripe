# Changelog

All notable changes to Pinstripe are documented here.

## Unreleased

- Changed: the front-page deploy layer pins HELMET ^0.3, PROCTOR ^0.4 and CREED ^0.1 (lock: HELMET 0.3.0, PROCTOR 0.4.0, CREED 0.1.6).
- Changed: CI pins Preflight 0.1.4 (GitLab component includes and the GitHub reusable workflow tag).

## 0.1.2 (2026-11-03)

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

## 0.1.1 (2026-10-29)

- Fixed the raw and Laravel PHPStan cuts inheriting `reportMaybesInMethodSignatures` and `reportStaticMethodSignatures` from `phpstan/phpstan-strict-rules`, which made them report phpdoc method signature issues they should not; `base.neon` now pins both to false and `strict.neon` keeps them on.
- Pinned the other eight parameters `phpstan/phpstan-strict-rules` forces on: four low-noise ones stay on in every cut, and `checkDynamicProperties`, `reportMaybesInPropertyPhpDocTypes`, `reportWrongPhpDocTypeInVarTag` and `reportNonIntStringArrayKey` are off in `base.neon` and on in `strict.neon`; the README lists each decision.

## 0.1.0 (2026-10-29)

- Added `bin/pinstripe-baseline`, which counts a PHPStan baseline's ignored errors (`count`) and fails when the baseline grows (`check --max=<n>` or `check --against=<git-ref>`), so baselines only shrink.
- Added `phpstan/strict.neon`, an opt-in PHPStan preset for new apps that turns on `phpstan/phpstan-strict-rules`.
- Added `tests/fixtures/per`, a Pint fixture proving the `per` preset is the PER Coding Style.
