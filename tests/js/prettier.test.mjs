import { test } from 'node:test'
import assert from 'node:assert/strict'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { readFile } from 'node:fs/promises'
import * as prettier from 'prettier'

// Runs the Pinstripe Prettier preset through Prettier's Node API (not a shell),
// so the tests behave the same on Windows, macOS and Linux.

const here = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(here, '..', '..')

const options = JSON.parse(await readFile(path.join(root, 'prettier', 'index.json'), 'utf8'))

/**
 * Check a source string against the preset, as if it lived at the given file.
 *
 * @param {string} source
 * @param {string} filepath
 * @return {Promise<boolean>}
 */
async function check(source, filepath) {
    return prettier.check(source, { ...options, filepath })
}

// Clean and messy twins, kept inline so the test has no fixture files to find.
const cleanJs = `export function greet(name) {
  const parts = ['Hello', name];
  return parts.join(', ');
}
`
const messyJs = `export function greet(name){const parts=["Hello",name];return parts.join(", ")}`

const cleanVue = `<script setup>
import { ref } from 'vue';

const count = ref(0);
</script>

<template>
  <button type="button" @click="count++">
    {{ count }}
  </button>
</template>
`
const messyVue = `<script setup>
import { ref } from "vue"
const count = ref(0)
</script>
<template><button type="button" @click="count++">{{count}}</button></template>
`

test('prettier preset: the clean JS fixture passes the check', async () => {
    assert.equal(await check(cleanJs, 'clean.js'), true)
})

test('prettier preset: the messy JS twin fails the check', async () => {
    assert.equal(await check(messyJs, 'messy.js'), false)
})

test('prettier preset: the clean Vue SFC passes the check', async () => {
    assert.equal(await check(cleanVue, 'clean.vue'), true)
})

test('prettier preset: the messy Vue twin fails the check', async () => {
    assert.equal(await check(messyVue, 'messy.vue'), false)
})

test('prettier preset: singleQuote takes effect', async () => {
    assert.equal(await check("const message = 'hello world';\n", 'quotes.js'), true)
    assert.equal(await check('const message = "hello world";\n', 'quotes.js'), false)
})

test('prettier preset: semi takes effect', async () => {
    assert.equal(await check("const message = 'hello world';\n", 'semi.js'), true)
    assert.equal(await check("const message = 'hello world'\n", 'semi.js'), false)
})

test('prettier preset: trailingComma all takes effect', async () => {
    assert.equal(await check('const settings = {\n  alpha: 1,\n  beta: 2,\n};\n', 'comma.js'), true)
    assert.equal(await check('const settings = {\n  alpha: 1,\n  beta: 2\n};\n', 'comma.js'), false)
})

test('prettier preset: printWidth is 100, not the default 80', async () => {
    // 89 characters: past Prettier's default width of 80, inside the preset's 100.
    const fits = "const items = ['alpha', 'bravo', 'charlie', 'delta', 'echo', 'foxtrot', 'golf', 'hotel'];\n"
    // 108 characters: past the preset's width, so the preset must break it.
    const tooLong = "const items = ['alpha', 'bravo', 'charlie', 'delta', 'echo', 'foxtrot', 'golf', 'hotel', 'india', 'juliet'];\n"
    assert.equal(await check(fits, 'width.js'), true)
    assert.equal(await check(tooLong, 'width.js'), false)
})
