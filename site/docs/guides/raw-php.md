---
title: "Raw PHP apps | Pinstripe docs"
description: "Use the Pinstripe presets in a raw PHP app, including Pint findings in the merge request."
url: https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# Raw PHP apps[#](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html#raw-php-apps)

Point a raw PHP app at the Pinstripe presets: Pint for style, PHPStan at level 8 for analysis.

## Add the dependency[#](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html#add-the-dependency)

Add Pinstripe as a dev dependency:

```bash
composer config allow-plugins.phpstan/extension-installer true
composer require --dev waynestate/pinstripe:^0.1
```

Pinstripe loads its PHPStan rules through `phpstan/extension-installer`, and Composer will not ask when it cannot prompt. If Composer stops because a package your app already locks (often an older PHPStan) does not fit, run the same `composer require` again with `-W`, as Composer suggests, so it may update that package too. Pinstripe 0.1.3 is on Packagist, and the npm package, `@waynestate/pinstripe`, is at 0.1.3.

## Point PHPStan at the raw config[#](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html#point-phpstan-at-the-raw-config)

`phpstan.neon`:

```neon
includes:
    - vendor/waynestate/pinstripe/phpstan/raw.neon

parameters:
    paths:
        - app
```

## Add Composer scripts[#](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html#add-composer-scripts)

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

`composer lint` fails on style. `composer fix` fixes it. Pint's `per` preset is the PER Coding Style, and `tests/fixtures/per` keeps a clean and a messy proof. `composer analyse` runs PHPStan at level 8, with deprecation checks too. Pinstripe ships `phpstan/phpstan-deprecation-rules`, loaded automatically by `phpstan/extension-installer`. Calling a `@deprecated` method fails the build. The tailor flags the old cut before the fabric is retired.

## Show Pint findings in the merge request[#](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html#show-pint-findings-in-the-merge-request)

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

## Related[#](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html#related)

- [Overview](https://pinstripe.apps.wayne.edu/docs/index.html)
- [Require strict types](https://pinstripe.apps.wayne.edu/docs/guides/strict-types.html)
- [Laravel apps](https://pinstripe.apps.wayne.edu/docs/guides/laravel.html)
- [The strict cut](https://pinstripe.apps.wayne.edu/docs/guides/strict-cut.html)
- [Baselines only shrink](https://pinstripe.apps.wayne.edu/docs/guides/baselines.html)
