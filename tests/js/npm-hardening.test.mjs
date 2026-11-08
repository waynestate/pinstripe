import { test } from 'node:test'
import assert from 'node:assert/strict'
import os from 'node:os'
import path from 'node:path'
import { execFile } from 'node:child_process'
import { existsSync } from 'node:fs'
import { access, mkdir, mkdtemp, rm, writeFile } from 'node:fs/promises'

// Proves the supply-chain guard Pinstripe tells apps to keep: with
// `npm install --ignore-scripts` a dependency's postinstall script does not
// run, and without the flag it does. The whole scenario is local (a file:
// package with no dependencies) and offline, so the test needs no network.

/**
 * Whether a file exists.
 *
 * @param {string} file
 * @return {Promise<boolean>}
 */
async function fileExists(file) {
    try {
        await access(file)
        return true
    } catch {
        return false
    }
}

/**
 * Locate npm's entry script so the test can run npm without a shell, the
 * same way the other JS tests avoid one. Official installs put npm beside
 * the node binary (Windows) or one level up under lib (Linux, macOS).
 *
 * @return {string} absolute path to npm-cli.js
 */
function npmCli() {
    const base = path.dirname(process.execPath)
    const candidates = [
        path.join(base, 'node_modules', 'npm', 'bin', 'npm-cli.js'),
        path.join(base, '..', 'lib', 'node_modules', 'npm', 'bin', 'npm-cli.js'),
    ]
    for (const candidate of candidates) {
        if (existsSync(candidate)) {
            return candidate
        }
    }
    throw new Error(`could not find npm-cli.js near ${process.execPath}; tried: ${candidates.join(', ')}`)
}

/**
 * Run npm in the given directory and resolve with its combined output.
 *
 * The environment drops every npm_config_* variable: when this suite runs
 * under `npm test`, npm exports the active config as environment variables,
 * including ignore-scripts=true from the repo's own .npmrc, and an uncleaned
 * child would inherit the very flag under test.
 *
 * @param {string} cwd directory to run npm in
 * @param {string[]} args npm arguments, for example ['install', '--offline']
 * @return {Promise<string>} combined stdout and stderr
 */
function runNpm(cwd, args) {
    const env = Object.fromEntries(Object.entries(process.env).filter(([key]) => !key.startsWith('npm_config_')))
    return new Promise((resolve, reject) => {
        execFile(process.execPath, [npmCli(), ...args], { cwd, env, maxBuffer: 10 * 1024 * 1024 }, (error, stdout, stderr) => {
            if (error !== null) {
                reject(new Error(`npm ${args.join(' ')} failed: ${error.message}\n${stdout}${stderr}`))
            } else {
                resolve(`${stdout}${stderr}`)
            }
        })
    })
}

test('npm install --ignore-scripts stops a postinstall script; without the flag it runs', async () => {
    const sandbox = await mkdtemp(path.join(os.tmpdir(), 'pinstripe-npm-hardening-'))
    try {
        // A local package whose postinstall drops a marker file where it is installed.
        const packageDir = path.join(sandbox, 'postinstall-mole')
        await mkdir(packageDir, { recursive: true })
        await writeFile(
            path.join(packageDir, 'package.json'),
            `${JSON.stringify({
                name: 'postinstall-mole',
                version: '1.0.0',
                scripts: { postinstall: 'node postinstall.cjs' },
            }, null, 4)}\n`,
        )
        await writeFile(
            path.join(packageDir, 'postinstall.cjs'),
            "require('node:fs').writeFileSync(require('node:path').join(__dirname, 'MARKER'), 'the mole dug\\n')\n",
        )

        // A consumer that depends on it through the file: protocol and on nothing else.
        const appDir = path.join(sandbox, 'app')
        await mkdir(appDir, { recursive: true })
        await writeFile(
            path.join(appDir, 'package.json'),
            `${JSON.stringify({
                name: 'consumer',
                version: '1.0.0',
                dependencies: { 'postinstall-mole': 'file:../postinstall-mole' },
            }, null, 4)}\n`,
        )

        const marker = path.join(appDir, 'node_modules', 'postinstall-mole', 'MARKER')

        // With the flag: the package installs, its postinstall does not run.
        await runNpm(appDir, ['install', '--ignore-scripts', '--offline', '--no-audit', '--no-fund'])
        assert.equal(await fileExists(marker), false, 'postinstall must not run with --ignore-scripts')

        // Without the flag: clear the tree so the install really happens again,
        // and the mole digs.
        await rm(path.join(appDir, 'node_modules'), { recursive: true, force: true })
        await runNpm(appDir, ['install', '--offline', '--no-audit', '--no-fund'])
        assert.equal(await fileExists(marker), true, 'postinstall must run when the flag is absent')
    } finally {
        await rm(sandbox, { recursive: true, force: true })
    }
})
