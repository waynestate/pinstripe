---
title: Raw PHP apps
description: Use the Pinstripe presets in a raw PHP app, including Pint findings in the merge request.
---

# Raw PHP apps

Point a raw PHP app at the Pinstripe presets: Pint for style, PHPStan at level 8 for analysis.

## Add the dependency

Add Pinstripe as a dev dependency:

<!-- docs-test: skip runs in the app, not in this repo -->

```bash
composer config allow-plugins.phpstan/extension-installer true
composer require --dev waynestate/pinstripe:^0.1
```

Pinstripe loads its PHPStan rules through `phpstan/extension-installer`, and Composer will not ask when it cannot prompt.
If Composer stops because a package your app already locks (often an older PHPStan) does not fit, run the same `composer require` again with `-W`, as Composer suggests, so it may update that package too.
Pinstripe 0.1.4 is on Packagist. The npm package, `@waynestate/pinstripe`, is published by hand after each tag; `npm view @waynestate/pinstripe version` shows where it is.

## Point PHPStan at the raw config

`phpstan.neon`:

```neon
includes:
    - vendor/waynestate/pinstripe/phpstan/raw.neon

parameters:
    paths:
        - app
```

## Add Composer scripts

Add Composer scripts that run on every OS:

```json
{
    "scripts": {
        "lint": "pint --test --config vendor/waynestate/pinstripe/pint/per.json",
        "fix": "pint --config vendor/waynestate/pinstripe/pint/per.json",
        "analyse": "phpstan analyse"
    }
}
```

`composer lint` fails on style. `composer fix` fixes it. Pint's `per` preset is the PER Coding Style, and `tests/fixtures/per` keeps a clean and a messy proof.
`composer analyse` runs PHPStan at level 8, with deprecation checks too.
Pinstripe ships `phpstan/phpstan-deprecation-rules`, loaded automatically by
`phpstan/extension-installer`. Calling a `@deprecated` method fails the build.
The tailor flags the old cut before the fabric is retired.
It also fails on `eval()`, `extract()`, `var_dump()`, `dd()` and `dump()`, and on TLS checks turned off through
curl (`CURLOPT_SSL_VERIFYPEER` false, `CURLOPT_SSL_VERIFYHOST` 0), a stream context (`verify_peer` false) or
Guzzle (`'verify' => false`), and on SQL joined from strings through PDO, mysqli or `oci_parse()` (bind the values
instead): the
[Banned calls](../reference/banned-calls.md) page says why and what to use instead.

## Show Pint findings in the merge request

Add a second script that writes the report to a file:

```json
{
    "scripts": {
        "lint:gitlab": "pint --test --config vendor/waynestate/pinstripe/pint/per.json --format=gitlab > pint-codequality.json"
    }
}
```

Point GitLab at the artifact:

```yaml
artifacts:
    reports:
        codequality: pint-codequality.json
```

`--format=gitlab` writes GitLab's Code Quality JSON and `[]` when the code is clean, and Pinstripe tests both. Chalk marks wash out; these findings stay pinned to the merge request.

## Related

- [Overview](../index.md)
- [Require strict types](strict-types.md)
- [Laravel apps](laravel.md)
- [The strict cut](strict-cut.md)
- [Baselines only shrink](baselines.md)
