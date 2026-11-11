import { test } from 'node:test'
import assert from 'node:assert/strict'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { readFile } from 'node:fs/promises'
import * as prettier from 'prettier'
import { ESLint } from 'eslint'

// Runs the Pinstripe presets through ESLint's Node API (not a shell), so the
// tests behave the same on Windows, macOS and Linux.

const here = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(here, '..', '..')

/**
 * Lint the given files (relative to the repo root) with one of the presets.
 *
 * @param {string} configFile path to the preset, relative to the repo root
 * @param {string[]} files files to lint, relative to the repo root
 * @return {Promise<import('eslint').ESLintResult[]>}
 */
async function lint(configFile, files) {
    const eslint = new ESLint({
        overrideConfigFile: path.join(root, configFile),
        cwd: root,
    })
    return eslint.lintFiles(files)
}

/** @param {import('eslint').ESLintResult} result */
function ruleIds(result) {
    return result.messages.map((message) => message.ruleId)
}

test('javascript preset: the clean fixture has no messages', async () => {
    const [result] = await lint('eslint/javascript.js', ['tests/js/clean-js/App.vue'])
    assert.deepEqual(result.messages, [], result.messages.map((m) => `${m.ruleId}: ${m.message}`).join('\n'))
})

test('typescript preset: the clean fixture has no messages', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/js/clean-ts/App.vue'])
    assert.deepEqual(result.messages, [], result.messages.map((m) => `${m.ruleId}: ${m.message}`).join('\n'))
})

test('typescript preset: a one-word component outside Pages still fails vue/multi-word-component-names', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/js/clean-ts/Index.vue'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('vue/multi-word-component-names'),
        `expected vue/multi-word-component-names, got: ${ids.join(', ')}`,
    )
})

test('typescript preset: an <img> without alt is flagged', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/js/a11y-bad/App.vue'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('vuejs-accessibility/alt-text'),
        `expected vuejs-accessibility/alt-text, got: ${ids.join(', ')}`,
    )
})

// The other TypeScript tests check Vue and accessibility rules only, so these prove the type-aware half of the preset (projectService) still runs.
test('typescript preset: a plain .ts file with its promises handled has no messages', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/js/typed/clean.ts'])
    assert.deepEqual(result.messages, [], result.messages.map((m) => `${m.ruleId}: ${m.message}`).join('\n'))
})

test('typescript preset: typed linting flags a floating promise in a .ts file', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/js/typed/floating.ts'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('@typescript-eslint/no-floating-promises'),
        `expected @typescript-eslint/no-floating-promises, got: ${ids.join(', ')}`,
    )
    for (const message of result.messages) {
        assert.equal(message.severity, 2, `${message.ruleId} should be an error: ${message.message}`)
    }
})

test('typescript preset: typed linting flags a floating promise in a .vue file', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/js/typed/FloatingSave.vue'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('@typescript-eslint/no-floating-promises'),
        `expected @typescript-eslint/no-floating-promises, got: ${ids.join(', ')}`,
    )
    for (const message of result.messages) {
        assert.equal(message.severity, 2, `${message.ruleId} should be an error: ${message.message}`)
    }
})

test('javascript preset: a v-for without :key is flagged', async () => {
    const [result] = await lint('eslint/javascript.js', ['tests/js/vue-bad/App.vue'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('vue/require-v-for-key'),
        `expected vue/require-v-for-key, got: ${ids.join(', ')}`,
    )
})

// The laravel-vue fixture is one app tree with both PHP and Vue files, so the
// PHP presets (Pint, PHPStan) and the JS presets (ESLint, Prettier) must agree
// on the same code. These tests cover the Vue side under resources/js.
test('laravel-vue fixture: the javascript preset accepts App.vue', async () => {
    const [result] = await lint('eslint/javascript.js', ['tests/fixtures/laravel-vue/resources/js/App.vue'])
    assert.deepEqual(result.messages, [], result.messages.map((m) => `${m.ruleId}: ${m.message}`).join('\n'))
})

test('laravel-vue fixture: the typescript preset accepts App.vue', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/fixtures/laravel-vue/resources/js/App.vue'])
    assert.deepEqual(result.messages, [], result.messages.map((m) => `${m.ruleId}: ${m.message}`).join('\n'))
})

test('laravel-vue fixture: the javascript preset flags bad/Broken.vue with vue/require-v-for-key', async () => {
    const [result] = await lint('eslint/javascript.js', ['tests/fixtures/laravel-vue/resources/js/bad/Broken.vue'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('vue/require-v-for-key'),
        `expected vue/require-v-for-key, got: ${ids.join(', ')}`,
    )
})

test('laravel-vue fixture: the typescript preset flags bad/NoAlt.vue with vuejs-accessibility/alt-text', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/fixtures/laravel-vue/resources/js/bad/NoAlt.vue'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('vuejs-accessibility/alt-text'),
        `expected vuejs-accessibility/alt-text, got: ${ids.join(', ')}`,
    )
})

test('laravel-vue fixture: App.vue is formatted to the Prettier preset', async () => {
    const options = JSON.parse(await readFile(path.join(root, 'prettier', 'index.json'), 'utf8'))
    const source = await readFile(path.join(root, 'tests/fixtures/laravel-vue/resources/js/App.vue'), 'utf8')
    assert.equal(await prettier.check(source, { ...options, filepath: 'App.vue' }), true)
})

// The laravel-inertia fixture is the same proof for WELD's shape, Laravel with
// Inertia and Vue: an Inertia controller and a page under resources/js/Pages.
// The PHP presets run under composer ci, these cover the Vue side.
test('laravel-inertia fixture: the typescript preset accepts Pages/Greetings/Index.vue', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/fixtures/laravel-inertia/resources/js/Pages/Greetings/Index.vue'])
    assert.deepEqual(result.messages, [], result.messages.map((m) => `${m.ruleId}: ${m.message}`).join('\n'))
})

test('laravel-inertia fixture: the javascript preset flags bad/Broken.vue with vue/require-v-for-key', async () => {
    const [result] = await lint('eslint/javascript.js', ['tests/fixtures/laravel-inertia/resources/js/bad/Broken.vue'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('vue/require-v-for-key'),
        `expected vue/require-v-for-key, got: ${ids.join(', ')}`,
    )
})

test('laravel-inertia fixture: the typescript preset flags bad/NoAlt.vue with vuejs-accessibility/alt-text', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/fixtures/laravel-inertia/resources/js/bad/NoAlt.vue'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('vuejs-accessibility/alt-text'),
        `expected vuejs-accessibility/alt-text, got: ${ids.join(', ')}`,
    )
})

test('laravel-inertia fixture: Pages/Greetings/Index.vue is formatted to the Prettier preset', async () => {
    const options = JSON.parse(await readFile(path.join(root, 'prettier', 'index.json'), 'utf8'))
    const source = await readFile(
        path.join(root, 'tests/fixtures/laravel-inertia/resources/js/Pages/Greetings/Index.vue'),
        'utf8',
    )
    assert.equal(await prettier.check(source, { ...options, filepath: 'Index.vue' }), true)
})

// The raw-vue fixture is the same proof for a plain PHP app with a Vue front
// end and no Laravel: the PHP presets run under composer ci, these cover the
// Vue side under resources/js.
test('raw-vue fixture: the javascript preset accepts App.vue', async () => {
    const [result] = await lint('eslint/javascript.js', ['tests/fixtures/raw-vue/resources/js/App.vue'])
    assert.deepEqual(result.messages, [], result.messages.map((m) => `${m.ruleId}: ${m.message}`).join('\n'))
})

test('raw-vue fixture: the typescript preset accepts App.vue', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/fixtures/raw-vue/resources/js/App.vue'])
    assert.deepEqual(result.messages, [], result.messages.map((m) => `${m.ruleId}: ${m.message}`).join('\n'))
})

test('raw-vue fixture: the javascript preset flags bad/Broken.vue with vue/require-v-for-key', async () => {
    const [result] = await lint('eslint/javascript.js', ['tests/fixtures/raw-vue/resources/js/bad/Broken.vue'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('vue/require-v-for-key'),
        `expected vue/require-v-for-key, got: ${ids.join(', ')}`,
    )
})

test('raw-vue fixture: the typescript preset flags bad/NoAlt.vue with vuejs-accessibility/alt-text', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/fixtures/raw-vue/resources/js/bad/NoAlt.vue'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('vuejs-accessibility/alt-text'),
        `expected vuejs-accessibility/alt-text, got: ${ids.join(', ')}`,
    )
})

test('raw-vue fixture: App.vue is formatted to the Prettier preset', async () => {
    const options = JSON.parse(await readFile(path.join(root, 'prettier', 'index.json'), 'utf8'))
    const source = await readFile(path.join(root, 'tests/fixtures/raw-vue/resources/js/App.vue'), 'utf8')
    assert.equal(await prettier.check(source, { ...options, filepath: 'App.vue' }), true)
})

// The warn-tier fixture violates only rules that the plugins' plain
// recommended configs reported as warnings: vue/attributes-order and
// vue/no-v-html. The strict presets must raise them to errors; the -warn
// presets must keep them as warnings.
const warnTierFixture = 'tests/js/warn-tier/App.vue'
const warnTierRules = ['vue/attributes-order', 'vue/no-v-html']

/** @param {import('eslint').ESLintResult} result */
function describeMessages(result) {
    return result.messages.map((m) => `${m.ruleId} (severity ${m.severity}): ${m.message}`).join('\n')
}

test('javascript preset: the warn-tier fixture is flagged, every message an error', async () => {
    const [result] = await lint('eslint/javascript.js', [warnTierFixture])
    assert.deepEqual(ruleIds(result).sort(), warnTierRules, describeMessages(result))
    for (const message of result.messages) {
        assert.equal(message.severity, 2, `${message.ruleId} should be an error: ${message.message}`)
    }
    assert.ok(result.errorCount > 0, describeMessages(result))
})

test('typescript preset: the warn-tier fixture is flagged, every message an error', async () => {
    const [result] = await lint('eslint/typescript.js', [warnTierFixture])
    assert.deepEqual(ruleIds(result).sort(), warnTierRules, describeMessages(result))
    for (const message of result.messages) {
        assert.equal(message.severity, 2, `${message.ruleId} should be an error: ${message.message}`)
    }
    assert.ok(result.errorCount > 0, describeMessages(result))
})

test('javascript-warn preset: the warn-tier fixture stays all warnings', async () => {
    const [result] = await lint('eslint/javascript-warn.js', [warnTierFixture])
    assert.deepEqual(ruleIds(result).sort(), warnTierRules, describeMessages(result))
    assert.equal(result.errorCount, 0, describeMessages(result))
    assert.ok(result.warningCount > 0, describeMessages(result))
})

test('typescript-warn preset: the warn-tier fixture stays all warnings', async () => {
    const [result] = await lint('eslint/typescript-warn.js', [warnTierFixture])
    assert.deepEqual(ruleIds(result).sort(), warnTierRules, describeMessages(result))
    assert.equal(result.errorCount, 0, describeMessages(result))
    assert.ok(result.warningCount > 0, describeMessages(result))
})
