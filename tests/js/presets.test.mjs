import { test } from 'node:test'
import assert from 'node:assert/strict'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
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

test('typescript preset: an <img> without alt is flagged', async () => {
    const [result] = await lint('eslint/typescript.js', ['tests/js/a11y-bad/App.vue'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('vuejs-accessibility/alt-text'),
        `expected vuejs-accessibility/alt-text, got: ${ids.join(', ')}`,
    )
})

test('javascript preset: a v-for without :key is flagged', async () => {
    const [result] = await lint('eslint/javascript.js', ['tests/js/vue-bad/App.vue'])
    const ids = ruleIds(result)
    assert.ok(
        ids.includes('vue/require-v-for-key'),
        `expected vue/require-v-for-key, got: ${ids.join(', ')}`,
    )
})
