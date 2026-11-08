---
title: Banned calls
description: The calls every Pinstripe cut fails the build on, why, what to use instead, and their OWASP Top 10:2025 tags.
---

# Banned calls

This page lists the calls every Pinstripe cut fails the build on, says why each is banned and what to use instead, and shows how to keep one on purpose.

## What is banned

Every cut (raw, Laravel and strict) includes `phpstan/disallowed.neon` through `base.neon`. The rules come from [spaze/phpstan-disallowed-calls](https://github.com/spaze/phpstan-disallowed-calls), which Pinstripe requires and `phpstan/extension-installer` loads, so your `phpstan.neon` needs no new line. A `dd()` left in after debugging turns the pipeline red, the same as any other PHPStan error.

| Call | Why it is banned | Use instead | OWASP Top 10:2025 |
|---|---|---|---|
| `eval()` | Runs a string as PHP, so one untrusted value is remote code execution. | Write the code out, or pick a callable with `match`. | A05 Injection |
| `extract()` | Turns array keys into variables, so request data can overwrite any variable in scope. | Read the keys you need by name, or destructure the array. | A05 Injection |
| `var_dump()` | Debug output left in ships paths, config and secrets to the browser. | A PSR-3 logger (`Log::debug()` in Laravel), or Xdebug. | A02 Security Misconfiguration |
| `dd()` | Dumps internals to the browser and halts the request in production. | The same. | A02 Security Misconfiguration |
| `dump()` | The same leak as `var_dump()`, Symfony and Laravel flavored. | The same. | A02 Security Misconfiguration |
| `withoutVerifying()` | Turns off TLS certificate checks in Laravel's HTTP client (`PendingRequest`), so anyone on the path can read or change the traffic. | Keep verification on; for a private CA, pass its bundle through `withOptions` and the `verify` option. | A02 Security Misconfiguration |

Each ban has a stable identifier: `pinstripe.banned.eval`, `pinstripe.banned.extract`, `pinstripe.banned.varDump`,
`pinstripe.banned.dd`, `pinstripe.banned.dump` and `pinstripe.banned.withoutVerifying`.

The `withoutVerifying()` ban catches both `Http::withoutVerifying()` and the method partway down a chain such as `Http::acceptJson()->withoutVerifying()`. Raw PHP apps never match it.

A report reads like this (wrapped here), with the reason, the fix and the OWASP tag in one message. Its tip links to this page and to the [CREED rule](https://creed.apps.wayne.edu/handbook/code.html#banned-patterns):

```text
Calling dd() is forbidden, a dd() left in dumps internals to the browser and halts the request in production;
log it with a PSR-3 logger (Log::debug() in Laravel), or step through with Xdebug (OWASP A02:2025 Security Misconfiguration).
```

## Keep one on purpose

Use the ban's identifier and say why in parentheses, as with any other ignore. Every cut reports an ignore without a reason.

```php
// @phpstan-ignore pinstripe.banned.varDump (CLI-only diagnostics; output goes to the operator's terminal)
var_dump($report);
```

On an existing app, fix the banned calls rather than baselining them: each is usually one line, and a baseline would hide the next one too.

## Add your own

List more bans under the same parameters (`disallowedFunctionCalls`, `disallowedMethodCalls`, `disallowedStaticCalls`) in your app's `phpstan.neon`. NEON appends lists, so yours join Pinstripe's. The package's [custom rules guide](https://github.com/spaze/phpstan-disallowed-calls/blob/main/docs/custom-rules.md) shows every key.

## Not covered yet

- The `dd()` and `dump()` methods on collections, queries and requests (`$users->dd()`).
- Turning verification off through `withOptions(['verify' => false])`.

## Not banned yet

`md5()`, `sha1()`, `rand()` and `mt_rand()` are weak for passwords, tokens and signatures, but they are fine for cache keys, checksums and other non-security uses, and apps across the fleet use them that way. Whether Pinstripe bans them (and how it would allow the safe uses) is an open call, so for now they pass.

## How it is proven

`composer ci` runs these on every change:

- `tests/fixtures/raw-disallowed/src/Leftovers.php` makes each banned call once, and the raw and strict cuts must report each one, by identifier, with its OWASP tag, and nothing else.
- Its clean twin, `LookAlikes.php`, defines methods named `dump()` and `dd()` and uses `var_export()` and `match`, and must stay green; `Excused.php` keeps one `var_dump()` with a reasoned ignore and must stay green too.
- `tests/fixtures/laravel/src/InsecureHttp.php` turns TLS checks off both ways, and the Laravel preset must report both.
- The Laravel app fixture installs Pinstripe through Composer, like an app does, and must fail on a leftover `dd()`.

A suit with a `dd()` in the pocket does not leave the shop.

## Related

- [Raw PHP apps](../guides/raw-php.md)
- [Laravel apps](../guides/laravel.md)
- [Adopting on an existing app](../guides/adopting.md)
- [Upgrading](../upgrading.md)
