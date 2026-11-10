import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import pluginVueA11y from 'eslint-plugin-vuejs-accessibility'
import prettierConfig from 'eslint-config-prettier'
import globals from 'globals'

// Pinstripe's JavaScript preset for Vue, keeping the plugins' original
// severities: findings the plugins' recommended configs report as warnings
// stay warnings.
//
// Spread this into your eslint.config.js while you adopt the strict preset
// gradually:
//
//   import pinstripe from '@waynestate/pinstripe/eslint/javascript-warn'
//   export default [...pinstripe]
//
// It lints plain JavaScript and .vue files with <script> (no lang="ts").
// For TypeScript, use @waynestate/pinstripe/eslint/typescript-warn instead.
// Once the codebase is clean, switch to
// @waynestate/pinstripe/eslint/javascript, where every Vue and
// accessibility finding fails lint.
export default [
    // Built assets and generated directories are never linted.
    {
        ignores: ['vendor/', 'node_modules/', 'public/build/', 'bootstrap/cache/', 'storage/'],
    },
    // Core JavaScript rules.
    js.configs.recommended,
    // Vue 3 rules, flat/recommended. vue-eslint-parser stays the top-level parser.
    ...pluginVue.configs['flat/recommended'],
    // Accessibility rules for .vue templates.
    ...pluginVueA11y.configs['flat/recommended'],
    // Inertia page components are rendered by name from the server
    // (Inertia::render('Greetings/Index')), never used as a tag, so a
    // one-word name like Index or Show cannot clash with an HTML element.
    {
        files: ['**/Pages/**/*.vue', '**/pages/**/*.vue'],
        rules: {
            'vue/multi-word-component-names': 'off',
        },
    },
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
