# Changelog

All notable changes to Pinstripe are documented here.

## Unreleased

- Added `bin/pinstripe-baseline`, which counts a PHPStan baseline's ignored errors (`count`) and fails when the baseline grows (`check --max=<n>` or `check --against=<git-ref>`), so baselines only shrink.
- Added `phpstan/strict.neon`, an opt-in PHPStan preset for new apps that turns on `phpstan/phpstan-strict-rules`.
- Added `tests/fixtures/per`, a Pint fixture proving the `per` preset is the PER Coding Style.
