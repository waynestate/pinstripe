<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use PHPUnit\Framework\TestCase;

/**
 * BEST_PRACTICES.md is only worth reading if it is true, and the docs page
 * is only worth reading if it says the same. This keeps both honest: every
 * test the table names exists in the class it names, every file it names is
 * there, each row has one known status, an Enforced row names its proof, and
 * docs/reference/best-practices.md carries the table word for word. A suit
 * and its reflection should match.
 */
final class BestPracticesTest extends TestCase
{
    private const STATUSES = ['Enforced', 'Checked', 'Documented', 'Not yet'];

    public function testEveryTestNamedHereExists(): void
    {
        preg_match_all('/`([A-Z][A-Za-z0-9]*Test)::(test[A-Za-z0-9_]+)`/', $this->bestPractices(), $matches, PREG_SET_ORDER);
        $this->assertGreaterThanOrEqual(20, count($matches), 'expected the table to name its tests as `Class::method`');

        $missing = [];

        foreach ($matches as [, $class, $method]) {
            $file = $this->root() . '/tests/' . $class . '.php';

            if (! is_file($file) || preg_match('/\bfunction ' . preg_quote($method, '/') . '\(/', (string) file_get_contents($file)) !== 1) {
                $missing[] = $class . '::' . $method;
            }
        }

        $this->assertSame([], $missing, 'BEST_PRACTICES.md names tests that do not exist');
    }

    public function testEveryFileNamedHereExists(): void
    {
        preg_match_all('/`(tests\/[A-Za-z0-9_\/.-]+)`/', $this->bestPractices(), $matches);
        $this->assertNotSame([], $matches[1]);

        $missing = array_values(array_filter(
            array_unique($matches[1]),
            fn (string $path): bool => ! is_file($this->root() . '/' . $path)
        ));

        $this->assertSame([], $missing, 'BEST_PRACTICES.md names files that do not exist');
    }

    public function testEveryRowHasAKnownStatusAndAnEnforcedRowNamesItsTests(): void
    {
        $rows = $this->rows();
        $this->assertGreaterThanOrEqual(15, count($rows), 'expected the preset table in BEST_PRACTICES.md');

        $wrong = [];

        foreach ($rows as [$preset, , $status, $proof]) {
            if (! in_array($status, self::STATUSES, true)) {
                $wrong[] = $preset . ' (status ' . $status . ')';
            } elseif ($status === 'Enforced' && preg_match('/`(?:[A-Z][A-Za-z0-9]*Test::test[A-Za-z0-9_]+|tests\/[^`]+)`/', $proof) !== 1) {
                $wrong[] = $preset . ' (Enforced, but names no test)';
            }
        }

        $this->assertSame([], $wrong, 'rows with an unknown status, or Enforced with no test named');
    }

    public function testTheDocsPageCarriesTheSameTable(): void
    {
        $content = $this->bestPractices();
        $body = rtrim(substr($content, (int) strpos($content, "\n") + 1), "\n");
        $page = str_replace("\r\n", "\n", (string) file_get_contents($this->root() . '/docs/reference/best-practices.md'));

        $this->assertStringContainsString($body, $page, 'docs/reference/best-practices.md must carry BEST_PRACTICES.md below its title, word for word');
    }

    private function root(): string
    {
        return dirname(__DIR__);
    }

    private function bestPractices(): string
    {
        $path = $this->root() . '/BEST_PRACTICES.md';
        $this->assertFileExists($path, 'BEST_PRACTICES.md must sit at the repository root');

        return str_replace("\r\n", "\n", (string) file_get_contents($path));
    }

    /**
     * The table's body rows, each as [preset, tool, status, enforced by].
     *
     * @return list<array{0: string, 1: string, 2: string, 3: string}>
     */
    private function rows(): array
    {
        $rows = [];

        foreach (explode("\n", $this->bestPractices()) as $line) {
            if (! str_starts_with($line, '| ') || str_starts_with($line, '| Preset ') || str_starts_with($line, '| ---')) {
                continue;
            }

            $cells = array_map('trim', explode(' | ', trim($line, '| ')));
            $this->assertCount(4, $cells, 'a row in BEST_PRACTICES.md does not have four cells: ' . $line);
            $rows[] = [$cells[0], $cells[1], $cells[2], $cells[3]];
        }

        return $rows;
    }
}
