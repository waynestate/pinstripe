# Pinstripe

The coding standard for the Wayne State app stack: shared presets for Pint, PHPStan (with Larastan for Laravel), Rector,
and ESLint with Prettier. Your app points at them and adds only its own exceptions. Pinstripe is to code what a good
suit is to a job interview: nobody notices it, everybody notices when it's missing.

Status: being scaffolded. The decisions it encodes are in the stack's ADR-0013:

| | Laravel app | Raw PHP app |
|---|---|---|
| Pint preset | `laravel` | `per` (PER Coding Style 3.1) |
| PHPStan | level 8, with Larastan | level 8 |
| Existing apps | adopt with a baseline that may only shrink | same |

Vue code is linted as JavaScript or TypeScript, and the stack's Vue starters use TypeScript.

The same commands work on Windows, macOS and Linux: `vendor/bin/pint`, `vendor/bin/phpstan`, `vendor/bin/rector` and
`npm run lint`. No bash required, no tailor either.
