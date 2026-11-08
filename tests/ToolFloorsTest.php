<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Keeps the "Tool floors" tables in the README in step with the lock files.
 * The Needs string of every row must equal the require.php of the locked
 * package in composer.lock (packages and packages-dev) or the engines.node
 * of node_modules/<name> in package-lock.json, so a dependency bump fails
 * the build until the table is re-cut in the same change.
 */
final class ToolFloorsTest extends TestCase
{
    /**
     * The eleven tools the README lists: the seven PHP tools from composer.lock
     * and the four Node tools from package-lock.json.
     */
    private const TOOLS = [
        'laravel/pint',
        'phpstan/phpstan',
        'phpstan/phpstan-strict-rules',
        'phpstan/phpstan-deprecation-rules',
        'larastan/larastan',
        'rector/rector',
        'spaze/phpstan-disallowed-calls',
        'eslint',
        'eslint-plugin-vue',
        'typescript-eslint',
        'prettier',
    ];

    public function testToolFloorsMatchTheLockFiles(): void
    {
        $floors = $this->toolFloorsFromReadme();

        foreach (self::TOOLS as $name) {
            $lock = $this->floorInLockFiles($name);

            $this->assertNotNull(
                $lock,
                sprintf('could not find %s in composer.lock or package-lock.json', $name)
            );

            $readme = $floors[$name] ?? null;

            $this->assertSame(
                $lock,
                $readme,
                sprintf(
                    'README "Tool floors" is out of date for %s: lock says "%s", README says "%s"',
                    $name,
                    $lock,
                    $readme === null ? '(missing)' : $readme
                )
            );
        }
    }

    /**
     * The name => needs map from the "Tool floors" tables in README.md:
     * every row whose first cell is a package name wrapped in backticks and
     * whose last cell is a string wrapped in backticks. An escaped pipe
     * (backslash-pipe) inside a cell is part of the string, not a column
     * separator.
     */
    private function toolFloorsFromReadme(): array
    {
        $floors = [];

        foreach ($this->sectionLines('Tool floors') as $line) {
            $cells = $this->tableCells($line);

            if ($cells === null || count($cells) < 2) {
                continue;
            }

            $name = $this->backticked($cells[0]);
            $needs = $this->backticked($cells[count($cells) - 1]);

            if ($name !== null && $needs !== null) {
                $floors[$name] = $needs;
            }
        }

        return $floors;
    }

    /**
     * The lines of the level-two section named $title in README.md, up to
     * the next level-two heading. Fails when the section is missing.
     */
    private function sectionLines(string $title): array
    {
        $lines = preg_split('/\R/', $this->readRootFile('README.md')) ?: [];
        $section = [];
        $inside = false;

        foreach ($lines as $line) {
            if (preg_match('/^## (.+)$/', trim($line), $matches) === 1) {
                if ($inside) {
                    break;
                }

                $inside = $matches[1] === $title;

                continue;
            }

            if ($inside) {
                $section[] = $line;
            }
        }

        $this->assertNotEmpty($section, 'README.md has no "## ' . $title . '" section');

        return $section;
    }

    /**
     * The cells of a Markdown table row, or null when the line is not a
     * table row. A backslash-pipe inside a cell is an escaped pipe, not a
     * separator.
     */
    private function tableCells(string $line): ?array
    {
        $line = trim($line);

        if (!str_starts_with($line, '|') || !str_ends_with($line, '|')) {
            return null;
        }

        $cells = [];

        foreach (preg_split('/(?<!\\\\)\|/', substr($line, 1, -1)) ?: [] as $cell) {
            $cells[] = str_replace('\\|', '|', trim($cell));
        }

        return $cells;
    }

    /**
     * The contents of a single backtick-wrapped cell such as ^8.3.0, or
     * null when the cell is not one backtick-wrapped string.
     */
    private function backticked(string $cell): ?string
    {
        if (preg_match('/^\x60([^\x60]+)\x60$/', $cell, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    /**
     * The floor the lock files declare for a tool: the require.php of the
     * package in composer.lock (packages and packages-dev), or the
     * engines.node of node_modules/<name> in package-lock.json. Null when
     * neither lock file names the tool with a floor.
     */
    private function floorInLockFiles(string $name): ?string
    {
        $composer = json_decode($this->readRootFile('composer.lock'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($composer, 'composer.lock is not a JSON object');

        foreach (['packages', 'packages-dev'] as $key) {
            foreach ($composer[$key] ?? [] as $package) {
                if (($package['name'] ?? null) === $name && isset($package['require']['php'])) {
                    return $package['require']['php'];
                }
            }
        }

        $lock = json_decode($this->readRootFile('package-lock.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($lock, 'package-lock.json is not a JSON object');

        $node = $lock['packages']['node_modules/' . $name]['engines']['node'] ?? null;

        return is_string($node) ? $node : null;
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
