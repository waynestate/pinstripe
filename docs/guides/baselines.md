---
title: Baselines only shrink
description: How baselines work in Pinstripe, and why they only shrink.
---

# Baselines only shrink

Keep a PHPStan baseline from growing by counting it and failing the build the moment it does.

Pinstripe ships `pinstripe-baseline`, one PHP command that runs on every OS, to keep that promise. It reads both the NEON baseline and the PHP format (`phpstan-baseline.php`).

## Opt in first

A baseline is only for apps adopting Pinstripe late; new apps never set it. The app opts in from its own `composer.json`:

```json
{
    "extra": {
        "pinstripe": {
            "legacy": true
        }
    }
}
```

Every command checks the flag before it reads the baseline. Without it, `pinstripe-baseline` exits 2 and says how to add it. In a monorepo, point the command at another composer.json with `--composer=<path>` (default: `composer.json` in the current working directory).

## Count the baseline

```
vendor/bin/pinstripe-baseline count phpstan-baseline.neon
```

prints how many errors the baseline ignores: the sum of each entry's `count`, 1 when `count` is absent.

## Check against a limit

```
vendor/bin/pinstripe-baseline check --max=42 phpstan-baseline.neon
```

exits 1 with `baseline grew: N errors, the most allowed is M` when the count is above the limit, 0 otherwise.

## Check against the target branch

To compare with the target branch instead of a fixed number, pass a git ref:

```
vendor/bin/pinstripe-baseline check --against=origin/main phpstan-baseline.neon
```

In GitLab CI the merge request's base SHA is already in the environment:

```
vendor/bin/pinstripe-baseline check --against=$CI_MERGE_REQUEST_DIFF_BASE_SHA phpstan-baseline.neon
```

## Update notice

After `count` and `check` finish, the command prints one line on stderr when a newer stable release of Pinstripe
exists:

```
pinstripe 0.4.0 is out of date: 0.5.0 is the newest release. Update: composer update waynestate/pinstripe
```

The update command matches the install: `git pull` in a source checkout, `composer global update
waynestate/pinstripe` for a Composer global install, or `composer require waynestate/pinstripe:^X.Y` (with `--dev`
when the pin is in require-dev) when the project's constraint excludes the newest release. When the newest release
has a higher major version, the line adds a link to [the upgrading guide](../upgrading.md).

The check asks Packagist at most once a day and caches the answer in your user cache dir. It never writes to stdout
and never changes an exit code. It stays silent in CI (when `CI` is set) and when `WAYNE_NO_UPDATE_CHECK=1` is set.

## The policy

An existing app may generate a baseline once with `phpstan analyse --generate-baseline` and include it in its `phpstan.neon`; new code gets no baseline. The count a baseline holds is the sum of each entry's `count`, 1 when `count` is absent, and `pinstripe-baseline count` prints it. `bin/pinstripe-baseline check` fails the build (exit 1) when the baseline grows, either against a fixed limit with `--max=<n>` or against a git ref with `--against=<git-ref>`; in GitLab CI the merge request's base SHA is already in the environment as `$CI_MERGE_REQUEST_DIFF_BASE_SHA`. Exit codes: 0 ok, 1 the baseline grew, 2 usage or baseline problem.

> [!NOTE]
> A baseline is a tailor's chalk line, and this command is the tape measure: it fails the build the moment the line gets longer. The policy behind this section is recorded in [the ADR](../adr/0001-phpstan-level-and-baseline-policy.md).

## Related

- [Adopting on an existing app](adopting.md)
- [The strict cut](strict-cut.md)
- [Raw PHP apps](raw-php.md)
- [Laravel apps](laravel.md)
