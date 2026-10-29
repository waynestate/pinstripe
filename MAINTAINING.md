# MAINTAINING: keeping the suit pressed

How to build, test, release and keep Pinstripe current. Pinstripe is the stack's coding standard: shared presets for
Pint, PHPStan (level 8), Rector, ESLint and Prettier. A pinstripe only looks sharp if the lines stay straight, so
changes here are small, tested, and announced.

## What Pinstripe is and what it depends on

- `waynestate/pinstripe` on Packagist: Pint presets in `pint/`, PHPStan presets in `phpstan/`, a Rector preset in
  `rector/`, and `bin/pinstripe-baseline` (counts and checks PHPStan baselines so they only shrink).
- `@waynestate/pinstripe` on npm: the ESLint presets in `eslint/` and the Prettier config in `prettier/`.
- Runtime dependencies (Composer): `laravel/pint`, `phpstan/phpstan`, `phpstan/extension-installer`,
  `phpstan/phpstan-strict-rules`, `phpstan/phpstan-deprecation-rules`, `nette/neon`. Larastan and Rector are
  suggested. npm peers: `eslint` and `prettier`; Node 22.13 or newer.
- Presets follow the stack's rules: Pint `laravel` for Laravel apps, `per` for raw PHP and framework-free packages;
  PHPStan level 8 for new code, with baselines for existing apps that may only shrink.
- **Who uses it:** the other stack packages as a dev dependency, and apps.

## Where it lives

Development happens on Wayne State's GitLab. This GitHub repository is a read-only release mirror: `main` and tags are
copied here automatically, and changes, issues and merge requests go to GitLab first. Nobody pushes to GitHub
directly.

## Build and test

| Task | Command |
|---|---|
| Install | `composer install` |
| All PHP checks (what CI runs) | `composer ci` |
| JS preset tests, Windows | `npm ci; npm test` |
| JS preset tests, Linux and macOS | `npm ci && npm test` |

With only Docker:

```
docker run --rm -v "${PWD}:/app" -w /app composer:2.10.3 sh -c "composer install --no-interaction && composer ci"
```

`composer ci` runs `composer validate --strict`, PHPUnit and `composer audit`. The PHPUnit suite is the real check:
it installs fixture apps and runs Pint and PHPStan with each preset against planted code, so a preset that
stops catching something fails a test. `npm test` (`node --test`) covers the JS presets and is **not** in CI yet, so
run it by hand when you touch `eslint/` or `prettier/`.

## CI

- **GitLab:** PREFLIGHT's `php-test` (PHP 8.3, 8.4, 8.5) and `composer-audit` components, plus a `composer-ci` job.
- **GitHub:** `.github/workflows/ci.yml` calls PREFLIGHT's reusable workflow with `composer ci`.
- Green means `composer ci` passed on all three PHP versions on both.

## Changing a preset

- A stricter preset breaks consumers' builds. Add the rule with a fixture that proves it, note it in the CHANGELOG,
  and ship it in a minor version so consumers opt in.
- Keep the "Tested with" versions in the README in step with what the fixtures actually install.
- When PHPStan, Pint, Larastan or Rector release a major version, bump the constraint here first and run the full
  suite.

## Releasing

1. Move the lines under `## Unreleased` in `CHANGELOG.md` to a new `## X.Y.Z (YYYY-MM-DD)` heading.
2. Set the same version in `package.json`.
3. `composer ci` and `npm test` green locally, then both pipelines green for that commit.
4. Run a secrets scan of the history and check that nothing internal is in the tree.
5. Tag `X.Y.Z` (no `v`) on GitLab. The mirror carries the tag to GitHub, and Packagist picks it up from there.
6. **npm is published by hand,** after the tag, from a clean checkout of the tagged commit, by an owner of the
   `@waynestate` npm scope with two-factor auth:
   ```
   npm ci --ignore-scripts
   npm test
   npm pack --dry-run
   npm publish --access public
   ```
   `npm pack --dry-run` should list only `eslint/`, `prettier/`, `README.md` and `package.json`. Publishing cannot be
   undone, so check the version first with `npm view @waynestate/pinstripe version`.
7. A release needs a maintainer's approval. Never move or delete a published tag.

## The front page

`site/` is the static front page, for `https://pinstripe.apps.wayne.edu/` on Citadel, served through a small raw-PHP
`deploy/` layer generated with SQUIRE.

## Who maintains it

Adam Perkins, Wayne State University Enterprise Applications. Internal runbook (accounts, tokens, mirrors, npm and
Packagist access): ask Enterprise Applications.
