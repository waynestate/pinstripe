# Changelog

All notable changes to Pinstripe are documented here.

## Unreleased

- Added: agent-ready site (prompt blocks, Markdown twins, llms.txt), from Squire's kit.
- Pinned symfony/mime v7.4.22 in composer.lock, the fixed version for CVE-2026-45070 (PKSA-wtxr-p26d-nn42, GHSA-vqc8-7275-q272), so the audit step of `composer ci` passes.
- Changed: project names written as words except acronyms.
- Changed: the deploy layer's Squire kit is refreshed to 0.3.3 (release options can now live in ./.env.squire).
- Changed: the deploy layer's Squire kit is refreshed to 0.3.4 (the local .env.squire file is now git-ignored).
- Fixed: stale capability claims in the site and README.

## 0.1.1 (2026-10-29)

- Fixed the raw and Laravel PHPStan cuts inheriting `reportMaybesInMethodSignatures` and `reportStaticMethodSignatures` from `phpstan/phpstan-strict-rules`, which made them report phpdoc method signature issues they should not; `base.neon` now pins both to false and `strict.neon` keeps them on.
- Pinned the other eight parameters `phpstan/phpstan-strict-rules` forces on: four low-noise ones stay on in every cut, and `checkDynamicProperties`, `reportMaybesInPropertyPhpDocTypes`, `reportWrongPhpDocTypeInVarTag` and `reportNonIntStringArrayKey` are off in `base.neon` and on in `strict.neon`; the README lists each decision.

## 0.1.0 (2026-10-29)

- Added `bin/pinstripe-baseline`, which counts a PHPStan baseline's ignored errors (`count`) and fails when the baseline grows (`check --max=<n>` or `check --against=<git-ref>`), so baselines only shrink.
- Added `phpstan/strict.neon`, an opt-in PHPStan preset for new apps that turns on `phpstan/phpstan-strict-rules`.
- Added `tests/fixtures/per`, a Pint fixture proving the `per` preset is the PER Coding Style.
