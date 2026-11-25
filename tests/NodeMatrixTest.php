<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Proves the JS presets on Node 22, 24 and 26. Pushes run Node 26 in the
 * npm-test job, and the full tier (nightly schedules, tags, web runs) adds
 * 22 and 24 in npm-test-matrix; the floor stays 22.13, from package.json
 * engines. A preset that only works on one Node major would otherwise
 * reach an app before CI notices.
 */
final class NodeMatrixTest extends TestCase
{
    /**
     * The Node majors the JS presets must run on across the two jobs.
     *
     * @var list<string>
     */
    private const NODE_MAJORS = ['22', '24', '26'];

    public function testGitLabRunsTheJsPresetsOnNode2224And26(): void
    {
        $gitlab = $this->readRootFile('.gitlab-ci.yml');

        $npmTest = $this->jobBlock($gitlab, 'npm-test');

        $this->assertNotSame(
            '',
            $npmTest,
            '.gitlab-ci.yml is missing the npm-test job: pushes must run the JS presets on Node 26.'
        );

        $this->assertMatchesRegularExpression(
            '/^\s*image:\s*\S*\/node:26$/m',
            $npmTest,
            '.gitlab-ci.yml npm-test must set an image line ending in /node:26 so pushes run the JS presets on Node 26.'
        );

        $this->assertStringNotContainsString(
            'parallel:',
            $npmTest,
            '.gitlab-ci.yml npm-test must not carry a parallel: key: pushes run one Node, 26, and the matrix lives in npm-test-matrix.'
        );

        $npmTestMatrix = $this->jobBlock($gitlab, 'npm-test-matrix');

        $this->assertNotSame(
            '',
            $npmTestMatrix,
            '.gitlab-ci.yml is missing the npm-test-matrix job: the full tier must add Node 22 and 24.'
        );

        $majors = $this->gitlabNodeMajors($npmTestMatrix);

        $this->assertSame(
            ['22', '24'],
            $majors,
            sprintf(
                '.gitlab-ci.yml npm-test-matrix must declare parallel: matrix: - NODE_MAJOR: [\'22\', \'24\'] in that order so the full tier adds Node 22 and 24 (found %s).',
                json_encode($majors, JSON_THROW_ON_ERROR)
            )
        );

        $this->assertMatchesRegularExpression(
            '/^\s*image:\s*\S*\/node:\$\{NODE_MAJOR\}$/m',
            $npmTestMatrix,
            '.gitlab-ci.yml npm-test-matrix must set an image line ending in /node:${NODE_MAJOR} so the matrix picks the Node major.'
        );

        $this->assertStringContainsString(
            "- if: '\$PREFLIGHT_TIER == \"full\"'",
            $npmTestMatrix,
            ".gitlab-ci.yml npm-test-matrix must carry the rule '- if: \$PREFLIGHT_TIER == \"full\"' so the matrix runs only on the full tier."
        );

        $covered = array_unique(array_merge(['26'], $majors));
        sort($covered);

        $this->assertSame(
            self::NODE_MAJORS,
            $covered,
            sprintf(
                '.gitlab-ci.yml must cover Node 22, 24 and 26 across npm-test (26) and npm-test-matrix (sorted union found %s).',
                json_encode($covered, JSON_THROW_ON_ERROR)
            )
        );
    }

    public function testThePipelineStillPinsNoNode22Image(): void
    {
        $contents = $this->readRootFile('.gitlab-ci.yml');

        $this->assertStringNotContainsString(
            'node:22',
            $contents,
            '.gitlab-ci.yml still pins a node:22 image: point the npm-test job at the matrix instead (node:${NODE_MAJOR}).'
        );
    }

    /**
     * The lines of one job's block in .gitlab-ci.yml: everything after the
     * column-0 '<job>:' line up to the next column-0 line, or '' when the
     * job is missing.
     */
    private function jobBlock(string $gitlab, string $job): string
    {
        $block = '';
        $inside = false;

        foreach (preg_split('/\R/', $gitlab) ?: [] as $line) {
            if (preg_match('/^\S/', $line) === 1) {
                $inside = rtrim($line) === $job . ':';
                continue;
            }

            if ($inside) {
                $block .= $line . "\n";
            }
        }

        return $block;
    }

    /**
     * The quoted values of the NODE_MAJOR flow list in a job block of
     * .gitlab-ci.yml, or [] when the block has no such matrix line.
     *
     * @return list<string>
     */
    private function gitlabNodeMajors(string $block): array
    {
        foreach (preg_split('/\R/', $block) ?: [] as $line) {
            if (preg_match('/^\s*-\s*NODE_MAJOR:\s*\[([^\]]*)\]\s*$/', $line, $matches) === 1) {
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
