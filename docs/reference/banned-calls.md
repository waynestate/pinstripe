---
title: Banned calls
description: The calls every Pinstripe cut fails the build on, why, what to use instead, and their OWASP Top 10:2025 tags.
---

# Banned calls

This page lists the calls every Pinstripe cut fails the build on, says why each is banned and what to use instead, and shows how to keep one on purpose.

## What is banned

Every cut (raw, Laravel and strict) includes `phpstan/disallowed.neon` through `base.neon`. The rules come from [spaze/phpstan-disallowed-calls](https://github.com/spaze/phpstan-disallowed-calls), which Pinstripe requires and `phpstan/extension-installer` loads, so your `phpstan.neon` needs no new line. A `dd()` left in after debugging turns the pipeline red, the same as any other PHPStan error.

The one exception is `curl_setopt()` with a verify option set to false: it needs the option and the value together, which that package cannot match, so Pinstripe ships a small rule of its own (`src/PHPStan/CurlVerifyOffRule.php`), registered in the same file. Its report reads and links like the others.

| Call | Why it is banned | Use instead | OWASP Top 10:2025 |
|---|---|---|---|
| `eval()` | Runs a string as PHP, so one untrusted value is remote code execution. | Write the code out, or pick a callable with `match`. | A05 Injection |
| `extract()` | Turns array keys into variables, so request data can overwrite any variable in scope. | Read the keys you need by name, or destructure the array. | A05 Injection |
| `var_dump()` | Debug output left in ships paths, config and secrets to the browser. | A PSR-3 logger (`Log::debug()` in Laravel), or Xdebug. | A02 Security Misconfiguration |
| `dd()` | Dumps internals to the browser and halts the request in production. | The same. | A02 Security Misconfiguration |
| `dump()` | The same leak as `var_dump()`, Symfony and Laravel flavored. | The same. | A02 Security Misconfiguration |
| Laravel's `->dd()` and `->dump()` methods | The same leaks, called on a collection, query, request or HTTP response (`$users->dd()`, `->dumpRawSql()`, `Http::get($url)->dd()`). | The same. | A02 Security Misconfiguration |
| `withoutVerifying()` | Turns off TLS certificate checks in Laravel's HTTP client (`PendingRequest`), so anyone on the path can read or change the traffic. | Keep verification on; for a private CA, pass its bundle through `withOptions` and the `verify` option. | A02 Security Misconfiguration |
| `'verify' => false` | The same hole through Guzzle's `verify` option: Laravel's `withOptions()`, `new Client([...])`, and the options of `request()`, `send()`, `get()`, `post()` and the other verbs. | Set `verify` to the path of the CA bundle, or leave it out. | A02 Security Misconfiguration |
| `curl_setopt()` and `curl_setopt_array()` with `CURLOPT_SSL_VERIFYPEER` false or `CURLOPT_SSL_VERIFYHOST` 0 | Turns off curl's certificate or host name check in raw PHP. | Keep both on; for a private CA, point `CURLOPT_CAINFO` at its bundle. | A02 Security Misconfiguration |
| `stream_context_create()` with `ssl` `verify_peer` or `verify_peer_name` false, or `allow_self_signed` true | Turns off the same checks for `file_get_contents()` and the other stream functions. | Keep them on; for a private CA, set the `ssl` `cafile` option. | A02 Security Misconfiguration |

Each ban has a stable identifier: `pinstripe.banned.eval`, `pinstripe.banned.extract`, `pinstripe.banned.varDump`,
`pinstripe.banned.dd`, `pinstripe.banned.dump`, `pinstripe.banned.withoutVerifying`, `pinstripe.banned.verifyFalse`, `pinstripe.banned.curlVerifyOff` and `pinstripe.banned.streamVerifyOff`.

Only the off switch is banned: `CURLOPT_SSL_VERIFYPEER` set to true, `verify` pointed at a CA bundle, and `false` on an option that has nothing to do with TLS all pass. The match is on literal values, so `'verify' => $verify`, or a curl option read from config, passes too; keep the value a literal where you can.

Laravel's methods share the functions' identifiers: `dd()`, `ddRawSql()`, `ddHeaders()`, `ddBody()`, `ddJson()` and `ddSession()` report as `pinstripe.banned.dd`, and `dump()`, `dumpRawSql()`, `dumpHeaders()` and `dumpSession()` as `pinstripe.banned.dump`. Facade calls such as `Http::dd()` count too. Only methods Laravel itself declares are caught, so your own `dump()` method is fine, and so is the schema dump behind `php artisan schema:dump`.

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

- An off switch PHPStan cannot pin to a value: `'verify' => $verify`, or a curl option read from config.

## Not banned yet

`md5()`, `sha1()`, `rand()` and `mt_rand()` are weak for passwords, tokens and signatures, but they are fine for cache keys, checksums and other non-security uses, and apps across the fleet use them that way. Whether Pinstripe bans them (and how it would allow the safe uses) is an open call, so for now they pass.

## How it is proven

`composer ci` runs these on every change:

- `tests/fixtures/raw-disallowed/src/Leftovers.php` makes each banned call once, and the raw and strict cuts must report each one, by identifier, with its OWASP tag, and nothing else.
- Its clean twin, `LookAlikes.php`, defines methods named `dump()` and `dd()` and uses `var_export()` and `match`, and must stay green; `Excused.php` keeps one `var_dump()` with a reasoned ignore and must stay green too.
- `tests/fixtures/laravel/src/LeftoverDebugMethods.php` leaves seven of Laravel's `dd()` and `dump()` methods in (on a collection, a query, a request, an HTTP response and the `Http` facade), and the Laravel preset must report each one; its twin `SchemaDump.php` writes a schema dump and must stay green.
- `tests/fixtures/laravel/src/InsecureHttp.php` turns TLS checks off both ways, and the Laravel preset must report both.
- `tests/fixtures/raw-disallowed/src/InsecureTransport.php` turns TLS checks off eight ways through curl and stream contexts, and the raw and strict cuts must report each one; its twin `SecureTransport.php` sets verification on and must stay green.
- `tests/fixtures/laravel/src/VerifyFalse.php` sets `verify` to false five ways through `withOptions()` and Guzzle, and the Laravel preset must report each one; its twin `VerifiedHttp.php` points `verify` at a CA bundle and must stay green.
- The Laravel app fixture installs Pinstripe through Composer, like an app does, and must fail on a leftover `dd()`, and on curl told to skip the certificate check (which proves Pinstripe's own curl rule loads from `vendor/`).

A suit with a `dd()` in the pocket does not leave the shop.

## Related

- [Raw PHP apps](../guides/raw-php.md)
- [Laravel apps](../guides/laravel.md)
- [Adopting on an existing app](../guides/adopting.md)
- [Upgrading](../upgrading.md)
