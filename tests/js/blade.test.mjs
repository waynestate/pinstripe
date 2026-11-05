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

// The filepath matches the preset's *.blade.php override, which selects the
// blade parser for these sources.
const bladePath = 'resources/views/welcome.blade.php'

// Clean and messy twins, kept inline so the test has no fixture files to find.
// The clean twin is Prettier's own output for the messy twin.
const cleanBlade = `<div>
  <p>Hello</p>
  @if ($a)
    <span>{{ $a }}</span>
  @endif
</div>
`
const messyBlade = `<div><p>Hello</p>@if($a)<span>{{ $a }}</span>@endif</div>
`

test('blade preset: a clean Blade view passes', async () => {
    assert.equal(await check(cleanBlade, bladePath), true)
})

test('blade preset: a messy Blade view fails', async () => {
    assert.equal(await check(messyBlade, bladePath), false)
})
