<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use PHPUnit\Framework\TestCase;

/**
 * The Upgrading page walks an app through every release: what changed for it,
 * the steps, and how to check. A release cut without its section leaves the
 * one reader who needs it (the app on the old version) with no fitting notes,
 * so this test holds docs/upgrading.md to CHANGELOG.md: every `## X.Y.Z (date)`
 * release there has a heading here with the same version and date, newest
 * first, in the same order. A draft `## Unreleased` section may lead the page;
 * the release commit renames it. No alterations without a fitting.
 */
final class UpgradingTest extends TestCase
{
    public function testEveryReleaseHasAnUpgradingSectionInTheSameOrder(): void
    {
        $releases = $this->releaseHeadings($this->read('CHANGELOG.md'));
        $sections = $this->releaseHeadings($this->read('docs/upgrading.md'));

        $this->assertNotSame([], $releases, 'CHANGELOG.md has no `## X.Y.Z (date)` release heading.');

        $missing = array_diff(array_keys($releases), array_keys($sections));
        $this->assertSame(
            [],
            array_values($missing),
            'docs/upgrading.md has no section for ' . implode(', ', $missing)
                . ': add `## X.Y.Z (date)` with what changed for an app, the steps and how to verify (rename the `## Unreleased` draft in the release commit).'
        );

        $extra = array_diff(array_keys($sections), array_keys($releases));
        $this->assertSame(
            [],
            array_values($extra),
            'docs/upgrading.md has a section for ' . implode(', ', $extra) . ', which CHANGELOG.md never released.'
        );

        $this->assertSame(
            array_keys($releases),
            array_keys($sections),
            'docs/upgrading.md lists the releases in a different order from CHANGELOG.md (newest first).'
        );

        foreach ($releases as $version => $date) {
            $this->assertSame(
                $date,
                $sections[$version],
                sprintf('docs/upgrading.md dates %s %s, CHANGELOG.md %s.', $version, $sections[$version] ?? '(none)', $date)
            );
        }
    }

    public function testAnUnreleasedDraftCanOnlyLeadThePage(): void
    {
        $headings = [];
        foreach (preg_split('/\R/', $this->read('docs/upgrading.md')) ?: [] as $line) {
            if (preg_match('/^## (Unreleased|\d+\.\d+\.\d+\b.*)$/', trim($line), $matches) === 1) {
                $headings[] = $matches[1];
            }
        }

        $drafts = array_keys($headings, 'Unreleased', true);
        $this->assertContains(
            $drafts,
            [[], [0]],
            'docs/upgrading.md may carry one `## Unreleased` draft, above every release, never between them.'
        );
    }

    /**
     * Version => date for every `## X.Y.Z (YYYY-MM-DD)` heading, in file order.
     *
     * @return array<string, string>
     */
    private function releaseHeadings(string $markdown): array
    {
        $headings = [];
        foreach (preg_split('/\R/', $markdown) ?: [] as $line) {
            $line = trim($line);
            if (preg_match('/^## (\d+\.\d+\.\d+)\b/', $line, $version) !== 1) {
                continue;
            }
            $this->assertSame(
                1,
                preg_match('/^## \d+\.\d+\.\d+ \((\d{4}-\d{2}-\d{2})\)$/', $line, $date),
                'a release heading needs its date, as `## X.Y.Z (YYYY-MM-DD)`: ' . $line
            );
            $this->assertArrayNotHasKey($version[1], $headings, 'two headings for ' . $version[1]);
            $headings[$version[1]] = $date[1];
        }

        return $headings;
    }

    private function read(string $path): string
    {
        $contents = file_get_contents(dirname(__DIR__) . '/' . $path);
        $this->assertNotFalse($contents, 'could not read ' . $path);

        return $contents;
    }
}
