---
title: "Pinstripe: Sharp code. Every line."
description: "Pinstripe is the coding standard for the Wayne State app stack: shared presets for Pint, PHPStan, Larastan, Rector, ESLint and Prettier, cut once and worn by every repo."
url: https://pinstripe.apps.wayne.edu/
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

Bespoke standards for the Wayne State app stack

# Pinstripe

Sharp code. Every line.

Shared presets for **Pint**, **PHPStan** with Larastan, **Rector**, **ESLint** for Vue and **Prettier**, cut once and worn by every repo in the stack and every app it generates. Your app points at them and adds only its own exceptions.

[Book a fitting](https://pinstripe.apps.wayne.edu/#fitting) [Try it on today](https://pinstripe.apps.wayne.edu/#try)

**Already included in [WELD](https://weld.apps.wayne.edu/).** Use Pinstripe on its own to give an app you already have the stack's Pint, PHPStan, ESLint and Prettier settings.

On Packagist and npm now. The shop is open; walk-ins welcome.

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
5. PHPStan · level 8 `$nick` might be null. PHPStan points; you sew the fix.

Pressed 0 errors at level 8. Nothing changed but the fit.

Cut from Pinstripe's own test fixtures (`raw-messy`, `rector-old`, `raw-nullbug`), where the real tools run on every `composer ci`.

Every preset is a piece of the suit

## The pieces

Seven measurements, one order card. Status comes straight from `BEST_PRACTICES.md`: a piece is **Enforced** when a test runs the real tool and checks both directions.

1. The cut Pint
   `pint/per.json` `pint/laravel.json`
   PER for raw PHP, the Laravel preset for Laravel.
   Enforced
2. The inseam PHPStan
   `phpstan/raw.neon` `phpstan/laravel.neon`
   Level 8 for new code. Every ignore needs a comment.
   Enforced
3. The lining Larastan
   `phpstan/laravel.neon`
   Sews itself in through the extension installer. No include line to keep.
   Enforced
4. The press Rector
   `rector/base.php`
   Reads your PHP floor from your own `composer.json`. Dead code, code quality, types, early returns.
   Enforced
5. The buttonholes ESLint
   `eslint/javascript.js` `eslint/typescript.js`
   Vue 3 recommended plus vuejs-accessibility. Every image gets its alt.
   Enforced
6. The hem Prettier
   `prettier/index.json`
   Single quotes, semicolons, 100 columns, trailing commas.
   Draft
7. The chalk line Baseline
   `phpstan analyse --generate-baseline`
   For an existing app: mark the fabric as it is, then take it in every release. Never let it out.
   Yours

The hem is still a draft: no test runs Prettier yet, so it is tacked, not sewn.

### Every piece has a mirror

A preset without a test is a suit without a mirror: you can't tell when it has gone wrinkled. So each one is tried on twice.

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

One bolt of cloth, three patterns

## Made to measure

Raw PHP, Laravel and Vue each get their own pattern. These are the files your app keeps; each pattern starts with its install line.

Piece A · cut 1

### Raw PHP

No framework. Pint with PER, PHPStan at level 8.

Install

```
composer require --dev waynestate/pinstripe:^0.1
```

Pinstripe 0.1.1 is on Packagist, so the line carries `^0.1`; bump it when a newer version ships.

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

The Laravel Pint preset, PHPStan with the Larastan lining.

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

Proven on a minimal Laravel 13 app in the fixtures, every run.

Piece C · cut 1

### Vue

ESLint flat config with vuejs-accessibility, Prettier for the layout.

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

Rector modernises code for the PHP version your app already supports. It reads the floor from your own `composer.json`, so it can't drift out of step. The press smooths old code to the standard without changing the cut.

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

Order slips, ready for the coding agent you already use. Copy one, or run it straight in OpenCode, Claude Code, Codex or ChatGPT. Each one names the pattern, what the agent may not let out, and tells it to ask before it guesses.

### Get started with Pinstripe

Measure the app, say which pieces fit, and chalk out every file before a stitch goes in.

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

Pint with PER, PHPStan at level 8, the three Composer scripts, and a baseline if the old cloth needs one.

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

The Laravel Pint preset and PHPStan with the Larastan lining, for Blade and Inertia apps alike.

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

ESLint with vuejs-accessibility and Prettier for the layout, on whichever app the Vue front end lives in.

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

The agent reads the same pages you do: [llms.txt](https://pinstripe.apps.wayne.edu/llms.txt) lists them, [llms-full.txt](https://pinstripe.apps.wayne.edu/llms-full.txt) holds them all in one file, and this page has a [Markdown twin](https://pinstripe.apps.wayne.edu/index.md). Nothing here phones home; the buttons only copy text or open the tool you picked. Measure twice, prompt once.

Real today

## Try it on

It's on the rack now; you can still try Pinstripe on in the shop: clone it and run its own checks. `composer ci` runs Pint, PHPStan, Larastan and Rector against the fixtures; `npm test` runs the ESLint presets.

The same commands on Windows, macOS and Linux. You need PHP 8.3 or newer, Composer, and Node 22.13 or newer.

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

Expect green on PHP 8.3, 8.4 and 8.5.

The whole wardrobe

## Worn across the stack

Every repo on this rail, and every app WELD and Sourdough turn out, is cut to Pinstripe.

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
  Starters you feed
- [WELD](https://weld.apps.wayne.edu/)
  The whole app, joined
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
