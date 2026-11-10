---
title: "Tested with and tool floors | Pinstripe docs"
description: "The tool versions Pinstripe is tested with, and the floors each tool needs."
url: https://pinstripe.apps.wayne.edu/docs/reference/tested-with.html
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# Tested with and tool floors [#](https://pinstripe.apps.wayne.edu/docs/reference/tested-with.html#tested-with-and-tool-floors)

This page records the tool versions Pinstripe is tested with and the PHP or Node floor each locked tool declares.

## Tested with [#](https://pinstripe.apps.wayne.edu/docs/reference/tested-with.html#tested-with)

- Pint 1.32.1
- PHPStan 2.3.3
- PHPStan extension-installer 1.4.3
- PHPStan strict-rules 2.1.0
- Larastan 3.13.2
- Rector 2.8.2
- PHPStan disallowed-calls 4.15.0
- ESLint 10.13.0
- Prettier 3.9.10
- eslint-plugin-vue 10.12.0
- eslint-plugin-vuejs-accessibility 2.6.3
- typescript-eslint 8.72.1
- Node 22, 24 and 26 (the JS presets run in CI on all three; the floor stays 22.13)

## Tool floors [#](https://pinstripe.apps.wayne.edu/docs/reference/tested-with.html#tool-floors)

Can your app run Pinstripe's tools? Each row shows the floor the locked package itself declares, read verbatim from `composer.lock` and `package-lock.json` today and checked against them by `tests/ToolFloorsTest.php`, so a dependency bump forces a README update in the same change.

PHP tools:

| Tool | Locked version | Needs |
| --- | --- | --- |
| `laravel/pint` | v1.32.1 | `^8.3.0` |
| `phpstan/phpstan` | 2.3.3 | `^7.4\|^8.0` |
| `phpstan/phpstan-strict-rules` | 2.1.0 | `^7.4 \|\| ^8.0` |
| `phpstan/phpstan-deprecation-rules` | 2.0.5 | `^7.4 \|\| ^8.0` |
| `larastan/larastan` | v3.13.2 | `^8.2` |
| `rector/rector` | 2.8.2 | `^7.4\|^8.0` |
| `spaze/phpstan-disallowed-calls` | v4.15.0 | `^7.4 \|\| ^8.0` |

Node tools:

| Tool | Locked version | Needs |
| --- | --- | --- |
| `eslint` | 10.13.0 | `^20.19.0 \|\| ^22.13.0 \|\| >=24` |
| `eslint-plugin-vue` | 10.12.0 | `^18.18.0 \|\| ^20.9.0 \|\| >=21.1.0` |
| `typescript-eslint` | 8.72.1 | `^18.18.0 \|\| ^20.9.0 \|\| >=21.1.0` |
| `prettier` | 3.9.10 | `>=14` |

- Pinstripe itself needs PHP 8.3 or newer and Node 22.13 or newer, so the Pint floor is the binding PHP floor.
- Pint only reads and rewrites files, so an app still on an older PHP can run Pint with a PHP 8.3 or newer binary (for example a container) while the app itself keeps its runtime.
- ESLint 10 is what sets the Node floor: Node 20 before 20.19 is out.

Measure twice, cut once: these floors are cut from the lock files, and the test keeps the hem honest.

## Related [#](https://pinstripe.apps.wayne.edu/docs/reference/tested-with.html#related)

- [Best practices](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html)
- [ADR-0001: PHPStan level 8 and baselines](https://pinstripe.apps.wayne.edu/docs/adr/0001-phpstan-level-and-baseline-policy.html)
- [Baselines only shrink](https://pinstripe.apps.wayne.edu/docs/guides/baselines.html)
- [Changelog](https://pinstripe.apps.wayne.edu/docs/changelog.html)
