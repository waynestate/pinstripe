---
title: "Proof"
description: "Pinstripe's presets run against 82 Wayne State applications on 2026-11-05: what ran, what held, what it caught in Pinstripe itself, what it does not prove, and how to run the same on your app."
---

# Proof

This page says how Pinstripe was proven on real Wayne State applications: what ran, what held, what the run caught in Pinstripe itself, and what it does not prove yet. Next, see [Adopting on an existing app](../guides/adopting.md) for the path the run followed and [Baselines only shrink](../guides/baselines.md) for the baseline rules.

A suit is proven at the fitting, not on the hanger. So on 2026-11-05 the presets were fitted to every PHP application in the stack's shared proof corpus, the same set PROCTOR, Squire and Preflight are measured on.

## Last run

| Check | Date | Commit | Result | Duration |
|---|---|---|---|---|
| `composer ci` (all five) | 2026-11-10 | e42cf2f | pass: 113 tests, 943 assertions, 1 skipped, exit 0 | 74 s |
| `composer validate --strict` | 2026-11-10 | e42cf2f | pass: composer.json is valid | 1 s |
| `php bin/agent-ready.php --check` | 2026-11-10 | e42cf2f | pass: 23 agent-ready files current | under 1 s |
| `composer audit` | 2026-11-10 | e42cf2f | pass: no security vulnerability advisories found | under 1 s |
| `php bin/docs-site.php --check` | 2026-11-10 | e42cf2f | pass: 18 docs pages current | under 1 s |
| `npm test` (ESLint and Prettier presets, Node 22) | 2026-11-10 | e42cf2f | pass: 36 of 36, including the raw-vue and laravel-inertia fixtures | 1 s after `npm ci` |

To refresh this table, regenerate the site with `composer docs` and `composer site:agent-ready`, rerun `composer ci`, and rewrite the rows with the new counts and the commit you ran on. The 82-application proof run described below did not run here: its corpus of internal application repositories is not reachable from this machine, and the npm side (ESLint and Prettier for Vue) runs in the npm-test CI job, not in `composer ci`; the `npm test` row was run by hand in `node:22`.

## What ran

Pinstripe 0.1.3, with the presets as they were on `main` on 2026-11-05 (later changes this run did not cover, such as the Inertia page rule in the Vue presets), with PHP 8.4. The 82 applications are 64 raw PHP apps (33 with a `composer.json`, 31 without one), 17 Laravel apps from 5.8 to 13, and 1 Slim 2 app. Laravel apps got the `laravel` presets; every other app got `per` and `raw`.

Each app was a throwaway copy of its default branch, checked out with Linux line endings, with `.env` files, `auth.json` and `config.files/` removed. Packagist was reachable; every Wayne State host was mapped to `127.0.0.1`, so nothing private could be fetched. Then, for each app:

1. Pint in test mode with the shape's preset, on the whole app, with a 15 minute limit;
2. for an app with a `composer.json`, the install the README gives: `composer require --dev waynestate/pinstripe:^0.1`, plus `larastan/larastan` for Laravel, and once more with `-W` when Composer itself said a locked package was in the way;
3. PHPStan with the preset: inside the app when the install worked, and otherwise, for a raw app, from a separate Pinstripe install pointed at the app's files;
4. where the install worked and PHPStan found errors: a baseline generated with `--generate-baseline`, PHPStan run again with it, and `pinstripe-baseline count` on it.

An app **passes** when Pint and PHPStan are both clean. It is **flagged** when either reports findings, or when the install could not finish for a reason the app owns. A **tool error** is a crash, a timeout, a report that is not valid, or a Pinstripe command that does not do what it says.

## Results

| Units | Pass | Flagged | Tool error |
|---|---|---|---|
| 82 applications | 0 | 81 | 1 |

No application was clean on day one, which is what a coding standard adopted late looks like. The tool error is a Pint timeout, described below.

| Step | What happened |
|---|---|
| Pint | Ran on all 82 apps and finished on 81, every one with findings: a median of 14 files to restyle, at most 180. The median app took 2 seconds. |
| Install | 12 of the 51 apps with a `composer.json` installed Pinstripe, one of them only with `-W`. The other 39 stopped on the app's own dependencies: 36 install a package from a private Git repository that the run could not reach, 2 need a PHP extension the run did not have, and for 1 Composer refused the update because the app's locked framework has security advisories. |
| PHPStan | Ran on 66 apps (12 inside the app, 54 raw apps from the separate install) and finished on all 66 with errors at level 8: a median of 86, at most 6,721. No crash, no memory limit hit. The median app took 11 seconds. The 16 Laravel apps that could not install were not analysed, because Larastan needs the app's own framework. |
| Baselines | Generated on all 12 apps that installed. With the baseline, PHPStan was green on 10. On the other 2 PHPStan said some errors could not be put into a baseline (classes that extend a class it could not find). `pinstripe-baseline count` read every baseline, and its count plus the errors PHPStan could not baseline equals PHPStan's total on every app. |

## What it caught in Pinstripe

A run that only finds praise was not looking hard enough. This one found these, and every finding is about Pinstripe, not the apps:

- **Fixed: `pinstripe-baseline count` printed an app's deprecations.** Installed in an app, the command loads the app's autoloader, and one app's old HTTP library printed PHP 8.4 deprecations ahead of the count. A script reading the number got a page of warnings. The command now mutes deprecations while it loads the autoloader, and a test covers it.
- **Documented: `-W`.** The install line failed on an app that already locked an older PHPStan, and Composer's own advice, `-W`, fixed it. The README and the guides now say so.
- **Documented: what a baseline cannot hold.** [Adopting on an existing app](../guides/adopting.md) now says what to do when PHPStan cannot baseline an error, and how to keep Pint off a library the app bundles.
- **Open: Pint on bundled libraries.** One app bundles a PDF library whose generated font maps are single arrays hundreds of kilobytes long. Pint spent over 100 seconds on one such file and did not finish the app in 15 minutes. That is Pint's fixer, not Pinstripe's preset, and the guide's advice is to point Pint at your own folders.
- **Open: Laravel apps that cannot install.** Larastan analyses a Laravel app through its framework, so an app whose dependencies cannot install gets no PHPStan result at all, while a raw app still gets one from a separate install.

## What this does not prove

- No app adopted Pinstripe for real: nothing was committed, and the restyling Pint proposed was not applied or reviewed.
- No private package could be fetched, so 36 apps never got past the install, and 16 Laravel apps were not analysed.
- The npm side (ESLint and Prettier for Vue) did not run here.
- The per-app record names the applications and stays in the stack's internal notes.

## Run it on your app

The same steps on your own app, from its root:

```sh
composer config allow-plugins.phpstan/extension-installer true
composer require --dev waynestate/pinstripe:^0.4
vendor/bin/pint --test --config vendor/waynestate/pinstripe/pint/per.json
vendor/bin/phpstan analyse
```

For a Laravel app, add `larastan/larastan` to the `require` line and use `pint/laravel.json`. Then follow [Adopting on an existing app](../guides/adopting.md) to baseline what is there and keep the new code clean.

## Related

- [Adopting on an existing app](../guides/adopting.md)
- [Baselines only shrink](../guides/baselines.md)
- [Raw PHP apps](../guides/raw-php.md)
- [Laravel apps](../guides/laravel.md)
