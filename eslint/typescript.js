import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import pluginVueA11y from 'eslint-plugin-vuejs-accessibility'
import prettierConfig from 'eslint-config-prettier'
import globals from 'globals'
import { withVueTs, vueTsConfigs } from '@vue/eslint-config-typescript'

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
// withVueTs is async (it resolves the .vue files in your project), so the
// preset awaits it and exports the resulting flat config array.
const pinstripe = await withVueTs(
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
