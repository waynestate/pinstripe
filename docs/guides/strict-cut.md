---
title: The strict cut
description: What the strict cut adds on top of the base presets.
---

# The strict cut

Point a new app at the strict PHPStan cut so loose comparisons and the strict rules fail the build.

## Choose the strict cut

New apps can include `vendor/waynestate/pinstripe/phpstan/strict.neon` instead of `raw.neon` or `laravel.neon`. It keeps the base cut and turns on `phpstan/phpstan-strict-rules`, so loose comparisons like `==` fail the build. Existing apps stay on the base cut and baseline their way over.

## What the strict cut adds

PHPStan checks phpdoc method signatures from level 3 up, and `phpstan/phpstan-strict-rules` forces the two switches behind them on for every run it is installed for. The base cut pins `reportMaybesInMethodSignatures` and `reportStaticMethodSignatures` off so it stays exactly as documented; the strict cut keeps them on, so it also reports maybe-mismatched overrides and static method overrides.

## The eight extra switches

`phpstan/phpstan-strict-rules` forces eight more switches on for every run as well. Each one is decided below: low noise and real bugs stay on in every cut, noisy ones are pinned off in the base cut and turned on only in `strict.neon`.

| Parameter | Raw and Laravel cuts | Strict cut | Why |
|---|---|---|---|
| `checkExplicitMixedMissingReturn` | on | on | A function declared to return `mixed` that can fall off the end is a real bug, and rare in practice. |
| `checkFunctionNameCase` | on | on | A wrongly cased function name works on Windows and macOS and breaks on Linux. |
| `checkInternalClassCaseSensitivity` | on | on | The same case bug for PHP's own classes. |
| `checkStrictPrintfPlaceholderTypes` | on | on | `%d` fed a string is a real formatting bug, and rare. |
| `checkDynamicProperties` | off | on | Floods legacy code with undeclared properties. |
| `reportMaybesInPropertyPhpDocTypes` | off | on | Reports "maybe" phpdoc mismatches that legacy docblocks trip constantly. |
| `reportWrongPhpDocTypeInVarTag` | off | on | Old `@var` tags are often loose; fixing them is cleanup, not bug hunting. |
| `reportNonIntStringArrayKey` | off | on | Bleeding-edge check that is noisy on loosely keyed arrays. |

> [!NOTE]
> Strict is the tailor's chalk line drawn before the first stitch.

## Related

- [Adopting on an existing app](adopting.md)
- [Baselines only shrink](baselines.md)
- [Raw PHP apps](raw-php.md)
- [Laravel apps](laravel.md)
- [ADR-0001: PHPStan level 8 and baselines](../adr/0001-phpstan-level-and-baseline-policy.md)
