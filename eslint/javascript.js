import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import pluginVueA11y from 'eslint-plugin-vuejs-accessibility'
import prettierConfig from 'eslint-config-prettier'
import globals from 'globals'
import { raiseWarnToError } from './lib/severity.js'

// Pinstripe's JavaScript preset for Vue.
//
// Spread this into your eslint.config.js:
//
//   import pinstripe from '@waynestate/pinstripe/eslint/javascript'
//   export default [...pinstripe]
//
// It lints plain JavaScript and .vue files with <script> (no lang="ts").
// For TypeScript, use @waynestate/pinstripe/eslint/typescript instead.
//
// Every Vue and accessibility finding fails lint: where the plugins'
// recommended configs report a rule as a warning, this preset reports it as
// an error, so the editor, a local run and CI agree on what fails. To keep
// the old behavior while you adopt gradually, use
// @waynestate/pinstripe/eslint/javascript-warn instead.
export default [
    // Built assets and generated directories are never linted.
    {
        ignores: ['vendor/', 'node_modules/', 'public/build/', 'bootstrap/cache/', 'storage/'],
    },
    // Core JavaScript rules.
    js.configs.recommended,
    // Vue 3 rules, flat/recommended-error: the recommended rules with every
    // warning raised to an error. vue-eslint-parser stays the top-level parser.
    ...pluginVue.configs['flat/recommended-error'],
    // Accessibility rules for .vue templates, with every warning raised to
    // an error.
    ...raiseWarnToError(pluginVueA11y.configs['flat/recommended']),
    {
        languageOptions: {
            globals: {
                ...globals.browser,
            },
        },
    },
    // Prettier owns layout. This must stay last so it turns off the rules that
    // would argue with it.
    prettierConfig,
]
