<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Proves the JS presets on Node 22, 24 and 26. The npm-test job on both
 * pipelines must run on all three Node majors while the floor stays 22.13,
 * from package.json engines: GitLab declares a parallel matrix over
 * NODE_MAJOR and GitHub a strategy matrix over node, and neither file may
 * pin a single node:22 image anymore. A preset that only works on one Node
 * major would otherwise reach an app before CI notices.
 */
final class NodeMatrixTest extends TestCase
{
    /**
     * The Node majors the npm-test job must run on.
     *
     * @var list<string>
     */
    private const NODE_MAJORS = ['22', '24', '26'];

    public function testGitLabRunsNpmTestOnNode2224And26(): void
    {
        $gitlab = $this->readRootFile('.gitlab-ci.yml');

        $majors = $this->gitlabNodeMajors($gitlab);

        $this->assertSame(
            self::NODE_MAJORS,
            $majors,
            sprintf(
                '.gitlab-ci.yml npm-test must declare parallel: matrix: - NODE_MAJOR: [\'22\', \'24\', \'26\'] so the JS presets run on Node 22, 24 and 26 (found %s).',
                json_encode($majors, JSON_THROW_ON_ERROR)
            )
        );

        $this->assertStringContainsString(
            'node:${NODE_MAJOR}',
            $gitlab,
            '.gitlab-ci.yml npm-test must set image: ${CI_DEPENDENCY_PROXY_GROUP_IMAGE_PREFIX}/node:${NODE_MAJOR} so the matrix picks the Node major.'
        );
    }

    public function testGitHubRunsNpmTestOnNode2224And26(): void
    {
        $workflow = $this->readRootFile('.github/workflows/ci.yml');

        $majors = $this->githubNodeMajors($workflow);

        $this->assertSame(
            self::NODE_MAJORS,
            $majors,
            sprintf(
                '.github/workflows/ci.yml npm-test must declare strategy: matrix: node: [\'22\', \'24\', \'26\'] so the JS presets run on Node 22, 24 and 26 (found %s).',
                json_encode($majors, JSON_THROW_ON_ERROR)
            )
        );

        $this->assertStringContainsString(
            'node:${{ matrix.node }}',
            $workflow,
            '.github/workflows/ci.yml npm-test must set container: node:${{ matrix.node }} so the matrix picks the Node major.'
        );
    }

    public function testNoPipelineStillPinsANode22Image(): void
    {
        foreach (['.gitlab-ci.yml', '.github/workflows/ci.yml'] as $file) {
            $contents = $this->readRootFile($file);

            $this->assertStringNotContainsString(
                'node:22',
                $contents,
                $file . ' still pins a node:22 image: point the npm-test job at the matrix instead (node:${NODE_MAJOR} on GitLab, node:${{ matrix.node }} on GitHub).'
            );
        }
    }

    /**
     * The quoted values of the NODE_MAJOR flow list under the npm-test job
     * in .gitlab-ci.yml, or [] when the job has no such matrix line.
     *
     * @return list<string>
     */
    private function gitlabNodeMajors(string $gitlab): array
    {
        $lines = preg_split('/\R/', $gitlab) ?: [];

        foreach ($lines as $index => $line) {
            if (trim($line) !== 'npm-test:') {
                continue;
            }

            foreach (array_slice($lines, $index + 1) as $later) {
                if (preg_match('/^\S/', $later) === 1) {
                    break;
                }

                if (preg_match('/^\s*-\s*NODE_MAJOR:\s*\[([^\]]*)\]\s*$/', $later, $matches) === 1) {
                    return $this->quotedValues($matches[1]);
                }
            }

            break;
        }

        return [];
    }

    /**
     * The quoted values of the node flow list under the strategy matrix of
     * the npm-test job in .github/workflows/ci.yml, or [] when the job has
     * no such matrix line.
     *
     * @return list<string>
     */
    private function githubNodeMajors(string $workflow): array
    {
        $lines = preg_split('/\R/', $workflow) ?: [];
        $jobIndent = null;
        $matrixIndent = null;

        foreach ($lines as $line) {
            if (trim($line) === 'npm-test:') {
                $jobIndent = $this->indentOf($line);
                $matrixIndent = null;

                continue;
            }

            if ($jobIndent === null || trim($line) === '') {
                continue;
            }

            $indent = $this->indentOf($line);

            if ($indent <= $jobIndent) {
                break;
            }

            if (preg_match('/^\s*matrix:\s*$/', $line) === 1) {
                $matrixIndent = $indent;

                continue;
            }

            if ($matrixIndent !== null && $indent <= $matrixIndent) {
                $matrixIndent = null;
            }

            if ($matrixIndent !== null && preg_match('/^\s*node:\s*\[([^\]]*)\]\s*$/', $line, $matches) === 1) {
                return $this->quotedValues($matches[1]);
            }
        }

        return [];
    }

    /**
     * The single- or double-quoted values in a YAML flow list body.
     *
     * @return list<string>
     */
    private function quotedValues(string $body): array
    {
        preg_match_all('/[\'"]([^\'"]+)[\'"]/', $body, $matches);

        return $matches[1] ?? [];
    }

    /**
     * The number of leading spaces of a line.
     */
    private function indentOf(string $line): int
    {
        return strlen($line) - strlen(ltrim($line, ' '));
    }

    private function readRootFile(string $name): string
    {
        $contents = file_get_contents($this->root() . '/' . $name);

        $this->assertNotFalse($contents, 'could not read ' . $name);

        return $contents;
    }

    private function root(): string
    {
        return dirname(__DIR__);
    }
}
