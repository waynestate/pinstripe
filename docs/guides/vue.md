---
title: Vue
description: Use the Pinstripe ESLint and Prettier presets for Vue.
---

# Vue

This guide shows how to point a Vue app at Pinstripe's ESLint and Prettier presets.

## Install

Add Pinstripe as a dev dependency:

```
npm install --save-dev @waynestate/pinstripe eslint prettier
```

## Configure ESLint

Create `eslint.config.js` and spread the preset. For TypeScript:

```js
import pinstripe from '@waynestate/pinstripe/eslint/typescript'

export default [...pinstripe]
```

For plain JavaScript, import `@waynestate/pinstripe/eslint/javascript` instead.

## Configure Prettier

Point Prettier at the preset in `package.json`:

```json
{
    "prettier": "@waynestate/pinstripe/prettier"
}
```

## Add scripts

Add scripts that run on every OS:

```json
{
    "scripts": {
        "lint": "eslint .",
        "format": "prettier --write ."
    }
}
```

`npm run lint` fails on style and accessibility, and on every Vue and accessibility finding: where the plugins' recommended configs report a rule as a warning, Pinstripe reports it as an error, so the editor, a local run and CI all agree on what fails. `npm run format` fixes the layout.

An app that cannot fix every finding in one cut can spread the `-warn` twin for a while (`@waynestate/pinstripe/eslint/javascript-warn` or `@waynestate/pinstripe/eslint/typescript-warn`): the same rules with the plugins' original severities, so findings that were warnings stay warnings. Switch back to the strict preset once the codebase is clean. The preset is the off-the-rack suit: it fits every app, and you only add your own cufflinks.

> [!NOTE]
> If an app adopts Oxlint for speed, ESLint stays in the toolchain for `.vue` files, because Pinstripe's Vue and accessibility rules (eslint-plugin-vue and eslint-plugin-vuejs-accessibility) run only in ESLint.

## Related

- [Raw PHP apps](raw-php.md)
- [The strict cut](strict-cut.md)
- [Best practices](../reference/best-practices.md)
