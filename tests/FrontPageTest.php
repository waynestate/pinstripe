<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use PHPUnit\Framework\TestCase;

/**
 * The front page's "Just off the cutting table" note tells a visitor what the
 * newest release brought. It once kept saying 0.3.0 two releases later, so
 * this test holds it to the newest `## X.Y.Z (date)` heading in CHANGELOG.md:
 * cut a release, and the build stays red until the note names it. Yesterday's
 * fashion belongs in the window display, not on the front page.
 */
final class FrontPageTest extends TestCase
{
    public function testTheCuttingTableNoteNamesTheNewestRelease(): void
    {
        $note = $this->cuttingTableNote();
        $release = $this->newestChangelogRelease();

        $this->assertNotNull($release, 'CHANGELOG.md has no `## X.Y.Z (date)` release heading.');
        $this->assertSame(1, preg_match('/\b(\d+\.\d+\.\d+)\b/', $note, $matches), 'the note names no version: ' . $note);
        $this->assertSame(
            $release,
            $matches[1],
            sprintf(
                'site/index.html\'s "Just off the cutting table" note leads with %s, but the newest CHANGELOG release is %s: say what %s brought.',
                $matches[1],
                $release,
                $release
            )
        );
    }

    public function testTheCuttingTableNoteLinksToUpgrading(): void
    {
        $this->assertStringContainsString('href="docs/upgrading.html"', $this->cuttingTableNote(true));
    }

    /**
     * The text of the one `<p class="try__next">` on the front page, tags
     * stripped unless $raw is true.
     */
    private function cuttingTableNote(bool $raw = false): string
    {
        $html = file_get_contents(dirname(__DIR__) . '/site/index.html');
        $this->assertNotFalse($html, 'could not read site/index.html');

        $count = preg_match_all('/<p class="try__next">(.*?)<\/p>/s', $html, $matches);
        $this->assertSame(1, $count, 'expected exactly one "Just off the cutting table" note on the front page');

        if ($raw) {
            return $matches[1][0];
        }

        $text = (string) preg_replace('/\s+/', ' ', strip_tags($matches[1][0]));

        // Skip the label itself, so the version the note leads with is the first one found.
        return trim(str_replace('Just off the cutting table', '', $text));
    }

    /**
     * The version in the first `## X.Y.Z (date)` heading of CHANGELOG.md,
     * skipping `## Unreleased`, or null when there is none.
     */
    private function newestChangelogRelease(): ?string
    {
        $changelog = file_get_contents(dirname(__DIR__) . '/CHANGELOG.md');
        $this->assertNotFalse($changelog, 'could not read CHANGELOG.md');

        foreach (preg_split('/\R/', $changelog) ?: [] as $line) {
            if (preg_match('/^## (\d+\.\d+\.\d+) \(\d{4}-\d{2}-\d{2}\)$/', trim($line), $matches) === 1) {
                return $matches[1];
            }
        }

        return null;
    }
}
