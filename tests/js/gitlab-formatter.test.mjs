import { test } from 'node:test'
import assert from 'node:assert/strict'
import os from 'node:os'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { mkdtemp, readFile, rm } from 'node:fs/promises'
import { ESLint } from 'eslint'

// Runs the gitlab formatter through ESLint's Node API (not a shell), so the
// tests behave the same on Windows, macOS and Linux. The formatter prints a
// console summary and writes the Code Quality report to a file: GitLab CI
// names it through CI_JOB_NAME plus the job's artifacts config, or the test
// points ESLINT_CODE_QUALITY_REPORT at a temp file instead.

const here = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(here, '..', '..')

/**
 * Lint one file with the javascript preset, run the gitlab formatter, and
 * return the parsed Code Quality report it wrote.
 *
 * @param {string} file the file to lint, relative to the repo root
 * @return {Promise<unknown>}
 */
async function gitlabReport(file) {
    const dir = await mkdtemp(path.join(os.tmpdir(), 'pinstripe-gitlab-'))
    const report = path.join(dir, 'gl-codequality.json')
    const saved = {
        CI_PROJECT_DIR: process.env.CI_PROJECT_DIR,
        ESLINT_CODE_QUALITY_REPORT: process.env.ESLINT_CODE_QUALITY_REPORT,
    }
    // CI_PROJECT_DIR keeps the report's paths relative to the repo root, and
    // ESLINT_CODE_QUALITY_REPORT names the artifact without reading a
    // .gitlab-ci.yml, the way GitLab CI sets both for every job.
    process.env.CI_PROJECT_DIR = root
    process.env.ESLINT_CODE_QUALITY_REPORT = report

    try {
        const eslint = new ESLint({
            overrideConfigFile: path.join(root, 'eslint/javascript.js'),
            cwd: root,
        })
        const results = await eslint.lintFiles([file])
        const formatter = await eslint.loadFormatter('gitlab')
        await formatter.format(results)

        return JSON.parse(await readFile(report, 'utf8'))
    } finally {
        for (const [name, value] of Object.entries(saved)) {
            if (value === undefined) {
                delete process.env[name]
            } else {
                process.env[name] = value
            }
        }
        await rm(dir, { recursive: true, force: true })
    }
}

test('gitlab formatter: a clean file gives an empty Code Quality report', async () => {
    // The other half of the test below: a report that always had findings in
    // it would turn every merge request red, so the clean fixture must give [].
    const issues = await gitlabReport('tests/js/clean-js/App.vue')
    assert.deepEqual(issues, [], 'the clean fixture gives an empty report')
})

test('gitlab formatter: ESLint findings become a GitLab Code Quality report', async () => {
    const issues = await gitlabReport('tests/js/vue-bad/App.vue')
    assert.ok(Array.isArray(issues), 'the report is a JSON array')
    assert.ok(issues.length > 0, 'the bad fixture produces at least one finding')

    for (const issue of issues) {
        assert.equal(typeof issue.description, 'string')
        assert.ok(issue.description.length > 0, 'every finding has a description')
        assert.equal(typeof issue.fingerprint, 'string')
        assert.ok(issue.fingerprint.length > 0, 'every finding has a fingerprint')
        assert.equal(typeof issue.severity, 'string')
        assert.ok(issue.severity.length > 0, 'every finding has a severity')
        assert.equal(typeof issue.location.path, 'string')
        assert.equal(
            issue.location.path.replace(/\\/g, '/'),
            'tests/js/vue-bad/App.vue',
            'the path is relative to the project directory',
        )
        assert.ok(Number.isInteger(issue.location.positions.begin.line))
        assert.ok(issue.location.positions.begin.line > 0, 'every finding has a begin line')
    }
})
