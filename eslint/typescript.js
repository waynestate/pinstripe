import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import pluginVueA11y from 'eslint-plugin-vuejs-accessibility'
import prettierConfig from 'eslint-config-prettier'
import globals from 'globals'
import { withVueTs, vueTsConfigs } from '@vue/eslint-config-typescript'
import { raiseWarnToError } from './lib/severity.js'

// Pinstripe's TypeScript preset for Vue.
//
// Spread this into your eslint.config.js:
//
//   import pinstripe from '@waynestate/pinstripe/eslint/typescript'
//   export default [...pinstripe]
//
// It lints .ts files and .vue files with <script lang="ts">, with typed
// linting (typescript-eslint recommendedTypeChecked, projectService). Typed
// linting is slower than syntax linting, so run it in CI, not on every save.
//
// Every <script> block in a .vue file must be TypeScript. For plain
// JavaScript, use @waynestate/pinstripe/eslint/javascript instead.
//
// Every Vue and accessibility finding fails lint: where the plugins'
// recommended configs report a rule as a warning, this preset reports it as
// an error, so the editor, a local run and CI agree on what fails. To keep
// the old behavior while you adopt gradually, use
// @waynestate/pinstripe/eslint/typescript-warn instead.
//
// withVueTs is async (it resolves the .vue files in your project), so the
// preset awaits it and exports the resulting flat config array.
const pinstripe = await withVueTs(
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
    // Inertia page components are rendered by name from the server
    // (Inertia::render('Greetings/Index')), never used as a tag, so a
    // one-word name like Index or Show cannot clash with an HTML element.
    {
        files: ['**/Pages/**/*.vue', '**/pages/**/*.vue'],
        rules: {
            'vue/multi-word-component-names': 'off',
        },
    },
    // typescript-eslint recommendedTypeChecked, adapted for .vue files.
    // This turns on typed linting (projectService) for .ts and .vue.
    vueTsConfigs.recommendedTypeChecked,
    {
        languageOptions: {
            globals: {
                ...globals.browser,
            },
        },
    },
    // Prettier owns layout. This must stay last so it turns off the rules
    // that would argue with it.
    prettierConfig,
)

export default pinstripe
