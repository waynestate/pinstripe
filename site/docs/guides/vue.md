---
title: "Vue | Pinstripe docs"
description: "Use the Pinstripe ESLint and Prettier presets for Vue."
url: https://pinstripe.apps.wayne.edu/docs/guides/vue.html
llms: https://pinstripe.apps.wayne.edu/llms.txt
generator: Squire agent-ready
---

# Vue[#](https://pinstripe.apps.wayne.edu/docs/guides/vue.html#vue)

This guide shows how to point a Vue app at Pinstripe's ESLint and Prettier presets.

## Install[#](https://pinstripe.apps.wayne.edu/docs/guides/vue.html#install)

Add Pinstripe as a dev dependency:

```
npm install --save-dev @waynestate/pinstripe eslint prettier
```

## Configure ESLint[#](https://pinstripe.apps.wayne.edu/docs/guides/vue.html#configure-eslint)

Create `eslint.config.js` and spread the preset. For TypeScript:

```js
import pinstripe from '@waynestate/pinstripe/eslint/typescript'

export default [...pinstripe]
```

For plain JavaScript, import `@waynestate/pinstripe/eslint/javascript` instead.

## Configure Prettier[#](https://pinstripe.apps.wayne.edu/docs/guides/vue.html#configure-prettier)

Point Prettier at the preset in `package.json`:

```json
{
    "prettier": "@waynestate/pinstripe/prettier"
}
```

## Add scripts[#](https://pinstripe.apps.wayne.edu/docs/guides/vue.html#add-scripts)

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

Note

If an app adopts Oxlint for speed, ESLint stays in the toolchain for `.vue` files, because Pinstripe's Vue and accessibility rules (eslint-plugin-vue and eslint-plugin-vuejs-accessibility) run only in ESLint.

## Related[#](https://pinstripe.apps.wayne.edu/docs/guides/vue.html#related)

- [Raw PHP apps](https://pinstripe.apps.wayne.edu/docs/guides/raw-php.html)
- [The strict cut](https://pinstripe.apps.wayne.edu/docs/guides/strict-cut.html)
- [Best practices](https://pinstripe.apps.wayne.edu/docs/reference/best-practices.html)
