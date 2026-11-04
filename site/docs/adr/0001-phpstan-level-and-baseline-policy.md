---
title: "ADR-0001: PHPStan level 8 and baselines that only shrink | Pinstripe docs"
url: https://pinstripe.apps.wayne.edu/docs/adr/0001-phpstan-level-and-baseline-policy.html
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# ADR-0001: PHPStan level 8 and baselines that only shrink[#](https://pinstripe.apps.wayne.edu/docs/adr/0001-phpstan-level-and-baseline-policy.html#adr-0001-phpstan-level-8-and-baselines-that-only-shrink)

Status: Accepted

## Context[#](https://pinstripe.apps.wayne.edu/docs/adr/0001-phpstan-level-and-baseline-policy.html#context)

Pinstripe is the coding standard for the Wayne State app stack, and PHPStan is its static analysis cut. Until now the level, the baseline policy and the place where the policy is enforced lived only in config comments and README prose, so each reader had to reassemble the decision from scattered notes.

Two populations need different handling: new code should be held to the full level from the first commit, while existing apps carry legacy findings they cannot fix in one pass. The policy has to say what each population owes and where the promise is checked.

The preset files already encode most of this: `phpstan/base.neon` carries the level and the ignore rules, the other presets are the cuts, and `bin/pinstripe-baseline` is the tool. What was missing was one document naming the decision.

## Decision[#](https://pinstripe.apps.wayne.edu/docs/adr/0001-phpstan-level-and-baseline-policy.html#decision)

- New code runs at PHPStan level 8, set once in `phpstan/base.neon`; the PHP version is inferred from the consumer's composer.json, not pinned in the preset.
- Every cut keeps `reportIgnoresWithoutComments` and `reportUnmatchedIgnoredErrors` on, so an `@phpstan-ignore` needs a reason in parentheses and must still match an error on its line.
- The base cut keeps phpstan-strict-rules off (`strictRules.allRules: false`) even though the package ships in `require` and the extension installer loads it for every run; `phpstan/strict.neon` is the opt-in strict cut that turns those rules back on.
- Of the extra parameters phpstan-strict-rules forces on, the four low-noise ones (`checkExplicitMixedMissingReturn`, `checkFunctionNameCase`, `checkInternalClassCaseSensitivity`, `checkStrictPrintfPlaceholderTypes`) stay on in every cut; the four noisy ones are pinned off in the base cut and turned on only in `phpstan/strict.neon`, and the README table records each decision.
- Laravel apps include `phpstan/laravel.neon`, which adds Larastan (registered through `phpstan/extension-installer`) and pins `noEnvCallsOutsideOfConfig` on; plain PHP apps include `phpstan/raw.neon`, which is the base cut alone.
- Pinstripe ships `phpstan/phpstan-deprecation-rules`, loaded automatically by `phpstan/extension-installer`, so calling a `@deprecated` method fails the build in every cut.
- New apps can include `phpstan/strict.neon` instead of `raw.neon` or `laravel.neon`; existing apps stay on the base cut and baseline their way over.
- An existing app may generate a baseline once with `phpstan analyse --generate-baseline` and include it in its `phpstan.neon`; new code gets no baseline. The baseline tool reads both the NEON baseline and the PHP format (`phpstan-baseline.php`).
- The count a baseline holds is the sum of each entry's `count`, 1 when `count` is absent, and `pinstripe-baseline count` prints it.
- `bin/pinstripe-baseline check` fails the build (exit 1) when the baseline grows, either against a fixed limit with `--max=<n>` or against a git ref with `--against=<git-ref>`; in GitLab CI the merge request's base SHA is already in the environment as `$CI_MERGE_REQUEST_DIFF_BASE_SHA`. Exit codes: 0 ok, 1 the baseline grew, 2 usage or baseline problem.
- Enforcement point, per `.gitlab-ci.yml`: a `composer-ci` job runs `composer install --no-interaction --no-progress --prefer-dist` and then `composer ci`, which runs the checks defined by the "ci" script in composer.json.
- Enforcement point, per the README's "Baselines only shrink" section: it shows the same `pinstripe-baseline check` command (with `--max` or `--against`) and says it fails the build the moment the line gets longer.

## Consequences[#](https://pinstripe.apps.wayne.edu/docs/adr/0001-phpstan-level-and-baseline-policy.html#consequences)

Good:

- Every app, new or existing, is held to one documented level, and the ignore-comment rules make every exception carry its reason and exit plan.
- Legacy debt is bounded and measurable: a baseline marks the fabric as it is, and the tape measure fails the build the instant it is let out.

Costs:

- Existing apps carry a baseline file they must keep shrinking, and every merge request needs the check wired in before the promise means anything.
- Level 8 with deprecation checks and the ignore-comment rules raises the bar for legacy code, so some apps fail until they fix their findings or record them in a baseline.

The chalk line stays on the suit, and we take it in every release: nobody in this shop sells extensions to the tape measure.
