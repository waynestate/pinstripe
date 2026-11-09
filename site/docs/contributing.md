---
title: "Contributing | Pinstripe docs"
description: "How to work on Pinstripe."
url: https://pinstripe.apps.wayne.edu/docs/contributing.html
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# Contributing[#](https://pinstripe.apps.wayne.edu/docs/contributing.html#contributing)

This page explains how to build, test and change Pinstripe, and the rules every change must keep.

## Build and test[#](https://pinstripe.apps.wayne.edu/docs/contributing.html#build-and-test)

| Task | Command |
| --- | --- |
| Install | `composer install` |
| All PHP checks (what CI runs) | `composer ci` |
| JS preset tests, Windows | `npm ci; npm test` |
| JS preset tests, Linux and macOS | `npm ci && npm test` |

With only Docker:

```
docker run --rm -v "${PWD}:/app" -w /app composer:2.11.1@sha256:8de2184c6de224f4689e8f80ed5f5edaf0030b6b8b7839a22a2bcb6722ac87d0 sh -c "composer install --no-interaction && composer ci"
```

`composer ci` runs `composer validate --strict`, PHPUnit, the agent-ready check (`php bin/agent-ready.php --check`), `composer audit` and the docs check (`php bin/docs-site.php --check`). The PHPUnit suite is the real check: it installs fixture apps and runs Pint, PHPStan and Rector with each preset against planted code, so a preset that stops catching something fails a test. `npm test` (`node --test`) covers the JS presets, and both pipelines run it on Node 22, 24 and 26, so a broken ESLint or Prettier preset fails the build too.

## CI[#](https://pinstripe.apps.wayne.edu/docs/contributing.html#ci)

- **GitLab:** Preflight's `php-test` (PHP 8.3, 8.4, 8.5), `composer-audit`, `proctor` (on `deploy/`) and `a11y` (on `site/`) components, pinned to 0.4.0, plus `composer-ci`, `npm-test` (Node 22, 24 and 26) and `wayneui-copies` jobs.
- **GitHub:** `.github/workflows/ci.yml` calls Preflight's reusable workflow (0.1.4) with `composer ci`, and runs `npm ci` then `npm test` in `node:22`, `node:24` and `node:26` containers.
- Green means `composer ci` passed on all three PHP versions and `npm test` passed on Node 22, 24 and 26, on both.

## Changing a preset[#](https://pinstripe.apps.wayne.edu/docs/contributing.html#changing-a-preset)

- A stricter preset breaks consumers' builds. Add the rule with a fixture that proves it, note it in the CHANGELOG, and ship it in a minor version so consumers opt in.
- Keep the "Tested with" versions in the README in step with what the fixtures actually install.
- When PHPStan, Pint, Larastan or Rector release a major version, bump the constraint here first and run the full suite.

## Rules a change must keep[#](https://pinstripe.apps.wayne.edu/docs/contributing.html#rules-a-change-must-keep)

Every preset is enforced by a test that runs the real tool against a fixture, so a preset that stops working fails `composer ci` here instead of failing a developer's app. A preset is **Enforced** when a test asserts both directions: a clean fixture passes, and a deliberately broken one is caught. The PHP presets run under `composer ci` (PHPUnit); the JavaScript presets run under `npm test`. Both run in CI on GitLab and GitHub, so a preset that stops working fails the pipeline before it reaches an app.

## Related[#](https://pinstripe.apps.wayne.edu/docs/contributing.html#related)

- [Best practices](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html)
- [Releasing](https://pinstripe.apps.wayne.edu/docs/deploying/releasing.html)
- [Upgrading](https://pinstripe.apps.wayne.edu/docs/upgrading.html)
- [Changelog](https://pinstripe.apps.wayne.edu/docs/changelog.html)
