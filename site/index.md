---
title: "Pinstripe: Sharp code. Every line."
description: "Pinstripe is the code style for the Wayne State app stack: shared presets for Pint, PHPStan with Larastan, Rector, ESLint and Prettier. Your app runs them locally, and Preflight runs them in CI."
url: https://pinstripe.apps.wayne.edu/
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

Code style for the Wayne State app stack

# Pinstripe

Sharp code. Every line.

Shared presets for **Pint**, **PHPStan** with Larastan, **Rector**, **ESLint** for Vue and **Prettier**. Your app's config points at them and adds only its own exceptions. You run them on your machine, and Preflight runs the same config in CI on every push.

[Book a fitting](https://pinstripe.apps.wayne.edu/#fitting) [Try it on today](https://pinstripe.apps.wayne.edu/#try)

**[WELD](https://weld.apps.wayne.edu/) already has it.** WELD starts with a copy of the Laravel presets. Install Pinstripe yourself to bring the same settings to an app you already have.

Version 0.1.1 is on Packagist and 0.1.0 is on npm. Walk-ins welcome.

Scroll, and the tailor gets to work

## The fitting

One PHP class before and after the fitting. Before: the constructor assigns a property by hand, greet() has no return type and its brace on the same line, the array uses array(), and strlen() is given a value that might be null. After: the property is promoted into the constructor, greet() returns string with its brace on its own line, the array is short, and the null case is handled.

As it walked in

```
final class Greeter
{
    private string $name;1

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function greet()2 {3
        $parts = array('Hello', $this->name)4;
        return implode(', ', $parts);
    }

    public function length(?string $nick): int
    {
        return strlen($nick)5;
    }
}
```

Pressed

```
final class Greeter
{
    public function __construct(private string $name)
    {
    }

    public function greet(): string
    {
        $parts = ['Hello', $this->name];
        return implode(', ', $parts);
    }

    public function length(?string $nick): int
    {
        return strlen($nick ?? '');
    }
}
```

Alterations No. 0.1

1. Rector Promote the property into the constructor.
2. Rector Add the return type, `: string`.
3. Pint The method's brace goes on its own line (PER).
4. Rector `array()` becomes `[]`.
5. PHPStan · level 8 `$nick` might be null. PHPStan only reports this one; you write the fix.

Pressed 0 errors at level 8, and the class still does what it did.

Cut from Pinstripe's own test fixtures (`raw-messy`, `rector-old`, `raw-nullbug`), where the real tools run on every `composer ci`.

Every preset is a piece of the suit

## The pieces

There are seven, each named for a part of the suit. The status comes from `BEST_PRACTICES.md`: a piece is **Enforced** when a test runs the real tool and checks that clean code passes and broken code fails.

1. The cut Pint
   `pint/per.json` `pint/laravel.json`
   The PER Coding Style for raw PHP, and Laravel's own preset for Laravel apps.
   Enforced
2. The inseam PHPStan
   `phpstan/raw.neon` `phpstan/laravel.neon`
   Level 8 for new code. An ignore without a comment is an error.
   Enforced
3. The lining Larastan
   `phpstan/laravel.neon`
   Loaded by `phpstan/extension-installer`, so there's no include line to add.
   Enforced
4. The press Rector
   `rector/base.php`
   Reads your PHP version from `composer.json`. It runs the dead code, code quality, type declaration and early return sets.
   Enforced
5. The buttonholes ESLint
   `eslint/javascript.js` `eslint/typescript.js`
   Vue 3's recommended rules plus vuejs-accessibility, so an image with no alt fails the lint.
   Enforced
6. The hem Prettier
   `prettier/index.json`
   Single quotes, semicolons, 100 columns, trailing commas.
   Draft
7. The chalk line Baseline
   `phpstan analyse --generate-baseline`
   For an existing app: record today's errors, then shrink the list. `pinstripe-baseline` fails the build if it grows.
   Yours

The hem is still a draft because no test runs Prettier yet. Consider it tacked.

### Every piece has a mirror

Each preset is tested on code it should accept and code it should reject. If a preset stops catching the bad code, the tests fail.

It fits

- `raw-clean` passes Pint and PHPStan
- `laravel-app` passes the Laravel presets
- `rector-clean` is left alone by Rector
- `clean-js` and `clean-ts` lint clean

It catches

- `raw-messy` fails Pint
- `raw-nullbug`: PHPStan reports the null
- `rector-old` gets altered
- an `<img>` without alt, a `v-for` without `:key`

Pick your pattern

## Made to measure

Each pattern starts with its install line, then shows the config files your app keeps. A Laravel app with a Vue front end uses two of them.

Piece A · cut 1

### Raw PHP

No framework. Pint with PER, PHPStan at level 8.

Install

```
composer require --dev waynestate/pinstripe:^0.1
```

`^0.1` gets you 0.1.1, the current release. Change it when 0.2 ships.

phpstan.neon

```
includes:
    - vendor/waynestate/pinstripe/phpstan/raw.neon

parameters:
    paths:
        - app
```

composer.json, scripts

```
"lint": "pint --test --config vendor/waynestate/pinstripe/pint/per.json",
"fix": "pint --config vendor/waynestate/pinstripe/pint/per.json",
"analyse": "phpstan analyse"
```

Composer scripts, so they run the same on Windows, macOS and Linux.

Piece B · cut 1

### Laravel

Laravel's Pint preset, and PHPStan with Larastan.

Install

```
composer require --dev waynestate/pinstripe:^0.1 larastan/larastan
```

phpstan.neon

```
includes:
    - vendor/waynestate/pinstripe/phpstan/laravel.neon
```

That's the whole file. Larastan registers itself through `phpstan/extension-installer`; allow the plugin when Composer asks. Scripts point at `pint/laravel.json`.

Every CI run checks these presets against a minimal Laravel 13 app in the fixtures.

Piece C · cut 1

### Vue

An ESLint flat config with the accessibility rules, and Prettier for formatting.

Install

```
npm install --save-dev @waynestate/pinstripe eslint prettier
```

eslint.config.js

```
import pinstripe from '@waynestate/pinstripe/eslint/typescript'

export default [...pinstripe]
```

Plain JavaScript? Import `eslint/javascript` instead.

package.json

```
"prettier": "@waynestate/pinstripe/prettier"
```

Alterations · fits A and B

### Rector

Rector updates old code to use what your app's current PHP version allows. It reads that version from your `composer.json`, so there's no second copy to keep in step. Moving to a newer PHP is Booster's job.

Install, alongside Pinstripe

```
composer require --dev rector/rector
```

rector.php

```
<?php

$base = require __DIR__ . '/vendor/waynestate/pinstripe/rector/base.php';

return $base->withPaths([__DIR__ . '/src']);
```

Hand it to your agent

## Fitted by your agent

Four prompts for whatever coding agent you use. Copy one, or open it in OpenCode, Claude Code, Codex or ChatGPT. Each one names the pattern and what the agent must leave alone, and tells it to ask you before it guesses.

### Get started with Pinstripe

Works out what kind of app this is and which presets fit, then lists every file it would add. It changes nothing yet.

A prompt for your coding agent. Copy it as it stands:

```text
You are a coding agent fitting Pinstripe to the app in this folder, at Wayne State University. Pinstripe is the coding standard of WELD, the Wayne State application stack: shared presets for Pint, PHPStan (with Larastan for Laravel), Rector, and ESLint with Prettier. The app points at them and adds only its own exceptions.

Read first:
- https://pinstripe.apps.wayne.edu/llms.txt, the index of Pinstripe's pages, each one as Markdown; llms-full.txt beside it holds the README with every install line. Fetch what you need; do not work from memory.
- ADVISOR, the stack's MCP server, if it is connected: read_project_docs for pinstripe answers from Pinstripe's own README, and list_stack_rules gives the CREED rules for this app's permutation. If it is not connected, say so and work from the pages. How to add it: https://advisor.apps.wayne.edu/llms.txt
- This app's composer.json, package.json if there is one, and any pint.json, phpstan.neon, rector.php, ESLint or Prettier config it already has.

Task:
1. Say which kind of app this is: raw PHP or Laravel (with Blade, or with Inertia), and whether it has a Vue front end.
2. Say which Pinstripe pieces fit: the raw cut or the Laravel cut for the PHP side, the ESLint and Prettier presets for Vue, and Rector if I want it.
3. List the exact files you would add or change, with their content, taken from the README for that kind of app. Change nothing yet.
4. If the app already has code, plan a PHPStan baseline: generated once, included, and only ever shrunk.

Constraints:
- Point at the presets in vendor/waynestate/pinstripe and node_modules/@waynestate/pinstripe. Never copy a preset into the app.
- PHPStan stays at level 8. Never lower it to get a run green; a baseline is how an existing app catches up.
- Use Composer and npm scripts, so every command runs the same on Windows, macOS and Linux. Where a command does differ, give the PowerShell and the bash form.
- Ask me before you guess: an app kind you cannot tell, an existing config that disagrees with a preset, or anything that touches CI or a deploy.

Output: the app kind, the pieces that fit, then the planned files as numbered steps, then your questions.
```

### Or cut from your pattern

### Pinstripe in a raw PHP app

Adds Pint with PER, PHPStan at level 8 and the Composer scripts, plus a baseline if existing code fails.

A prompt for your coding agent. Copy it as it stands:

```text
You are a coding agent adding Pinstripe to a raw PHP app (no framework) at Wayne State University. Pinstripe is the coding standard of WELD, the Wayne State application stack.

Read first:
- https://pinstripe.apps.wayne.edu/llms.txt, then the README in llms-full.txt beside it: its raw PHP section has every line you need.
- ADVISOR, the stack's MCP server, if it is connected: read_project_docs for pinstripe, and list_stack_rules with permutation raw-php (raw-php-vue if the app builds Vue). If it is not connected, say so and work from the pages.
- composer.json, and the folders that hold the app's PHP code.

Task:
1. Add Pinstripe with `composer require --dev waynestate/pinstripe:^0.1`.
2. Write phpstan.neon: include vendor/waynestate/pinstripe/phpstan/raw.neon and list the app's own code folders under paths.
3. Add the lint, fix and analyse Composer scripts from the README, with Pint pointed at vendor/waynestate/pinstripe/pint/per.json.
4. Run `composer lint` and `composer analyse`. If existing code fails, generate a baseline with `phpstan analyse --generate-baseline`, include it, and report its size with `vendor/bin/pinstripe-baseline count phpstan-baseline.neon`.
5. Only if I ask for Rector: `composer require --dev rector/rector`, a rector.php that uses Pinstripe's base preset with the app's own paths, then `vendor/bin/rector process --dry-run`, and show me the diff.

Constraints:
- The raw cut is Pint with PER and PHPStan at level 8. Never lower the level and never edit a preset; the app's own exceptions go in the app's own files.
- Run `composer fix` only when I say so: it rewrites files.
- Ask me before you guess, and before you touch CI or anything that deploys.

Output: the files you added or changed, the lint and analyse results (with the baseline count if there is one), then your questions.
```

### Pinstripe in a Laravel app

Adds Laravel's Pint preset and PHPStan with Larastan. It works for Blade apps and Inertia apps.

A prompt for your coding agent. Copy it as it stands:

```text
You are a coding agent adding Pinstripe to a Laravel app (with Blade, or with Inertia and Vue) at Wayne State University. Pinstripe is the coding standard of WELD, the Wayne State application stack.

Read first:
- https://pinstripe.apps.wayne.edu/llms.txt, then the README in llms-full.txt beside it: its Laravel section has every line you need.
- ADVISOR, the stack's MCP server, if it is connected: read_project_docs for pinstripe, and list_stack_rules with permutation laravel-blade, laravel-blade-vue or laravel-inertia-vue, whichever this app is. If it is not connected, say so and work from the pages.
- composer.json (config.allow-plugins included), and any pint.json and phpstan.neon the app already has.

Task:
1. Add Pinstripe and Larastan with `composer require --dev waynestate/pinstripe:^0.1 larastan/larastan`. Allow phpstan/extension-installer when Composer asks: it registers Larastan, so there is no Larastan line to add.
2. Make phpstan.neon include vendor/waynestate/pinstripe/phpstan/laravel.neon and nothing else, as the README shows.
3. Add lint, fix and analyse Composer scripts like the raw PHP ones in the README, with Pint pointed at vendor/waynestate/pinstripe/pint/laravel.json.
4. Run `composer lint` and `composer analyse`. If existing code fails, generate a baseline with `phpstan analyse --generate-baseline`, include it, and report its size with `vendor/bin/pinstripe-baseline count phpstan-baseline.neon`.
5. If the app has a Vue front end, say so: the ESLint and Prettier presets are a separate order.

Constraints:
- PHPStan stays at level 8 with the Larastan lining. Never lower it and never edit a preset.
- If the app already has a pint.json or phpstan.neon, show me what would change before you replace it.
- Ask me before you guess, and before you touch CI, migrations or anything that deploys.

Output: the files you added or changed, the lint and analyse results (with the baseline count if there is one), then your questions.
```

### Pinstripe on the Vue side

Adds the ESLint and Prettier presets to your Vue front end and reports the accessibility findings first.

A prompt for your coding agent. Copy it as it stands:

```text
You are a coding agent adding Pinstripe's ESLint and Prettier presets to the Vue front end of an app at Wayne State University: a Laravel app with Inertia or with Vue islands in Blade, or a raw PHP app with a Vue front end. Pinstripe is the coding standard of WELD, the Wayne State application stack.

Read first:
- https://pinstripe.apps.wayne.edu/llms.txt, then the README in llms-full.txt beside it: its Vue section has every line you need.
- ADVISOR, the stack's MCP server, if it is connected: read_project_docs for pinstripe, and policy_lookup for Node. If it is not connected, say so and work from the pages.
- package.json, its lock file, and any ESLint or Prettier config the app already has.

Task:
1. Check that Node is 22.13 or newer.
2. Add the presets with `npm install --save-dev @waynestate/pinstripe eslint prettier`.
3. Write eslint.config.js that spreads @waynestate/pinstripe/eslint/typescript, or @waynestate/pinstripe/eslint/javascript if the app has no TypeScript.
4. Point Prettier at @waynestate/pinstripe/prettier in package.json, and add the lint and format scripts from the README.
5. Run `npm run lint` and report the findings, the accessibility ones first. Run `npm run format` only when I say so: it rewrites files.

Constraints:
- The lock file stays committed; a missing one is a finding.
- Never turn an accessibility rule off to get a run green. Fix the markup, or ask me.
- Ask me before you guess, and before you replace an existing ESLint or Prettier config.

Output: the files you added or changed, the lint findings grouped by rule, then your questions.
```

The agent reads the same pages you do: [llms.txt](https://pinstripe.apps.wayne.edu/llms.txt) lists them, [llms-full.txt](https://pinstripe.apps.wayne.edu/llms-full.txt) holds them all in one file, and this page has a [Markdown twin](https://pinstripe.apps.wayne.edu/index.md). Nothing here phones home. The buttons only copy text or open the tool you picked.

Real today

## Try it on

To see the presets work before you add them to an app, clone Pinstripe and run its own checks. `composer ci` runs Pint, PHPStan, Larastan and Rector against the fixtures, and `npm test` runs the ESLint presets.

The commands are the same on Windows, macOS and Linux. You need PHP 8.3 or newer, Composer, and Node 22.13 or newer.

On the cutting table next Reading the PHP floor from CREED. coming soon

Fitting room any OS

```
$ git clone https://github.com/waynestate/pinstripe.git
$ cd pinstripe
$ composer install
$ composer ci
$ npm ci
$ npm test
```

CI runs this on PHP 8.3, 8.4 and 8.5.

The whole wardrobe

## Worn across the stack

Most of the stack's PHP projects check their own code with Pinstripe. WELD starts from a copy of its Laravel presets. Apps that Sourdough makes don't include it yet.

- [WayneUI](https://wayneui.apps.wayne.edu/)
  The interface
- [CREED](https://creed.apps.wayne.edu/)
  The written standards
- [PROCTOR](https://proctor.apps.wayne.edu/)
  Checks an app against them
- [Preflight](https://preflight.apps.wayne.edu/)
  Shared CI
- [SESAME](https://sesame.apps.wayne.edu/)
  Sign-in
- [HELMET](https://helmet.apps.wayne.edu/)
  Security headers
- Pinstripe
  The tailor (you are here)
- [Sourdough](https://sourdough.apps.wayne.edu/)
  Starts a raw PHP app
- [WELD](https://weld.apps.wayne.edu/)
  The Laravel and Vue starter
- [Squire](https://squire.apps.wayne.edu/)
  Carries the release
- [Rollcall](https://rollcall.apps.wayne.edu/)
  Every app answers present
- [ADVISOR](https://advisor.apps.wayne.edu/)
  What applies to your app
- [Drawbridge](https://drawbridge.apps.wayne.edu/)
  The sign-in gateway
- [Booster](https://booster.apps.wayne.edu/)
  Keeps your PHP current
