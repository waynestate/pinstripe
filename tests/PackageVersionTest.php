<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Keeps the npm package in step with the releases. The "version" in
 * package.json must equal the newest `## X.Y.Z (date)` heading in
 * CHANGELOG.md at all times, and on a tag pipeline (GitLab or GitHub) it
 * must equal the tag itself, so npm can actually publish from the tag.
 * composer ci runs this on both pipelines, so a release whose version was
 * never bumped fails the build instead of the publish.
 */
final class PackageVersionTest extends TestCase
{
    public function testVersionMatchesTheNewestChangelogRelease(): void
    {
        $release = $this->newestChangelogRelease();

        $this->assertNotNull(
            $release,
            'CHANGELOG.md has no `## X.Y.Z (date)` release heading; add one when you cut the release.'
        );

        $version = $this->packageVersion();

        $this->assertSame(
            $release,
            $version,
            sprintf(
                'package.json version %s does not match the newest CHANGELOG release %s: bump package.json in the release commit.',
                $version === null ? '(missing)' : $version,
                $release
            )
        );
    }

    public function testVersionMatchesTheTagOnATagPipeline(): void
    {
        $tag = $this->pipelineTag();

        if ($tag === null) {
            $this->markTestSkipped('Not a tag pipeline: CI_COMMIT_TAG is empty and GITHUB_REF_TYPE is not tag.');
        }

        $version = $this->packageVersion();

        $this->assertSame(
            $tag,
            $version,
            sprintf(
                'package.json version %s does not match the tag %s (tags have no v): bump package.json in the release commit.',
                $version === null ? '(missing)' : $version,
                $tag
            )
        );
    }

    /**
     * The "version" string in package.json, or null when the key is missing.
     */
    private function packageVersion(): ?string
    {
        $package = json_decode($this->readRootFile('package.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($package, 'package.json is not a JSON object');

        return isset($package['version']) && is_string($package['version']) ? $package['version'] : null;
    }

    /**
     * The version in the first `## X.Y.Z (date)` heading of CHANGELOG.md,
     * skipping `## Unreleased` and any other non-release heading, or null
     * when the changelog has no release yet.
     */
    private function newestChangelogRelease(): ?string
    {
        foreach (preg_split('/\R/', $this->readRootFile('CHANGELOG.md')) ?: [] as $line) {
            if (preg_match('/^## (\d+\.\d+\.\d+) \(\d{4}-\d{2}-\d{2}\)$/', trim($line), $matches) === 1) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * The tag this pipeline runs for: CI_COMMIT_TAG on GitLab, or
     * GITHUB_REF_TYPE=tag with GITHUB_REF_NAME on GitHub. Tags carry no `v`.
     * Null on branch and merge request pipelines.
     */
    private function pipelineTag(): ?string
    {
        $gitlabTag = getenv('CI_COMMIT_TAG');

        if (is_string($gitlabTag) && $gitlabTag !== '') {
            return $gitlabTag;
        }

        $refType = getenv('GITHUB_REF_TYPE');
        $refName = getenv('GITHUB_REF_NAME');

        if ($refType === 'tag' && is_string($refName) && $refName !== '') {
            return $refName;
        }

        return null;
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
