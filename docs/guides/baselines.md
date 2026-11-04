---
title: Baselines only shrink
description: How baselines work in Pinstripe, and why they only shrink.
---

# Baselines only shrink

Keep a PHPStan baseline from growing by counting it and failing the build the moment it does.

Pinstripe ships `pinstripe-baseline`, one PHP command that runs on every OS, to keep that promise. It reads both the NEON baseline and the PHP format (`phpstan-baseline.php`).

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

## The policy

An existing app may generate a baseline once with `phpstan analyse --generate-baseline` and include it in its `phpstan.neon`; new code gets no baseline. The count a baseline holds is the sum of each entry's `count`, 1 when `count` is absent, and `pinstripe-baseline count` prints it. `bin/pinstripe-baseline check` fails the build (exit 1) when the baseline grows, either against a fixed limit with `--max=<n>` or against a git ref with `--against=<git-ref>`; in GitLab CI the merge request's base SHA is already in the environment as `$CI_MERGE_REQUEST_DIFF_BASE_SHA`. Exit codes: 0 ok, 1 the baseline grew, 2 usage or baseline problem.

> [!NOTE]
> A baseline is a tailor's chalk line, and this command is the tape measure: it fails the build the moment the line gets longer. The policy behind this section is recorded in [the ADR](../adr/0001-phpstan-level-and-baseline-policy.md).

## Related

- [Adopting on an existing app](adopting.md)
- [The strict cut](strict-cut.md)
- [Raw PHP apps](raw-php.md)
- [Laravel apps](laravel.md)
