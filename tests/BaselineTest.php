<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WayneState\Pinstripe\Baseline;
use WayneState\Pinstripe\BaselineNotFoundException;

/**
 * Counts PHPStan baselines in both formats, and drives the pinstripe-baseline
 * script end to end for its exit codes, including `check --against=<ref>`
 * against a throwaway git repository built in a temp dir.
 *
 * Commands use the array form with proc_open and no shell, so the tests
 * behave the same on Windows, macOS and Linux.
 */
final class BaselineTest extends TestCase
{
    /**
     * Temp dirs created by this test, removed in tearDown.
     *
     * @var list<string>
     */
    private array $tempDirs = [];

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->tempDirs as $dir) {
            if (is_dir($dir)) {
                $this->removeDir($dir);
            }
        }

        $this->tempDirs = [];
    }

    /**
     * Run a command in the package root, or in $directory when given, and
     * return [exit code, combined output].
     *
     * @param list<string> $command
     *
     * @return array{0: int, 1: string}
     */
    private function runCommand(array $command, ?string $directory = null): array
    {
        $directory ??= $this->root();

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $directory
        );

        $this->assertIsResource($process, 'proc_open failed for: ' . implode(' ', $command));

        $stdout = stream_get_contents($pipes[1]) ?: '';
        $stderr = stream_get_contents($pipes[2]) ?: '';

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exit = proc_close($process);

        return [$exit, $stdout . $stderr];
    }

    private function root(): string
    {
        return dirname(__DIR__);
    }

    private function fixture(string $name): string
    {
        return $this->root() . '/tests/fixtures/baseline/' . $name;
    }

    /**
     * Run the pinstripe-baseline script with the given arguments, from the
     * package root or from $directory when given.
     *
     * @param list<string> $arguments
     *
     * @return array{0: int, 1: string}
     */
    private function runScript(array $arguments, ?string $directory = null): array
    {
        return $this->runCommand(
            array_merge([PHP_BINARY, $this->root() . '/bin/pinstripe-baseline'], $arguments),
            $directory
        );
    }

    /**
     * Run a git command in the given directory and return [exit code,
     * combined output].
     *
     * @param list<string> $arguments
     *
     * @return array{0: int, 1: string}
     */
    private function gitRun(string $dir, array $arguments): array
    {
        return $this->runCommand(array_merge(['git'], $arguments), $dir);
    }

    /**
     * Create a throwaway git repository in a temp dir and return its path.
     * The repository is local only: no remote, no network.
     */
    private function createRepo(): string
    {
        $dir = sys_get_temp_dir() . '/pinstripe-baseline-' . bin2hex(random_bytes(4));
        $this->assertTrue(mkdir($dir), 'could not create the temp dir ' . $dir);

        $this->tempDirs[] = $dir;

        $this->gitRun($dir, ['init', '-q']);
        $this->gitRun($dir, ['config', 'user.name', 'Pinstripe test']);
        $this->gitRun($dir, ['config', 'user.email', 'test@example.com']);

        return $dir;
    }

    /**
     * Write $name with $contents into the repository, commit it, and return
     * the new HEAD SHA.
     */
    private function commitFile(string $dir, string $name, string $contents): string
    {
        $this->assertNotFalse(file_put_contents($dir . '/' . $name, $contents));

        $this->gitRun($dir, ['add', $name]);

        [$exit, $output] = $this->gitRun($dir, ['commit', '-q', '-m', 'baseline']);
        $this->assertSame(0, $exit, $output);

        [$exit, $output] = $this->gitRun($dir, ['rev-parse', 'HEAD']);
        $this->assertSame(0, $exit, $output);

        return trim($output);
    }

    /**
     * A NEON baseline whose single entry ignores $count errors.
     */
    private function neonBaseline(int $count): string
    {
        return "parameters:\n    ignoreErrors:\n        - message: '#^Pinstripe test error$#'\n          count: {$count}\n";
    }

    /**
     * Remove a directory tree, contents first.
     */
    private function removeDir(string $dir): void
    {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }

    public function testCountSumsTheCountsInANeonBaseline(): void
    {
        $this->assertSame(6, Baseline::countFile($this->fixture('phpstan-baseline.neon')));
    }

    public function testCountAssumesOnePerEntryWhenCountsAreAbsent(): void
    {
        $this->assertSame(3, Baseline::countFile($this->fixture('phpstan-baseline-nocount.neon')));
    }

    public function testCountReadsThePhpBaselineFormat(): void
    {
        $this->assertSame(6, Baseline::countFile($this->fixture('phpstan-baseline.php')));
    }

    public function testCountThrowsForAMissingFile(): void
    {
        $this->expectException(BaselineNotFoundException::class);

        Baseline::countFile($this->fixture('nope.neon'));
    }

    public function testScriptPrintsTheCount(): void
    {
        [$exit, $output] = $this->runScript(['count', 'tests/fixtures/baseline/phpstan-baseline.neon']);

        $this->assertSame(0, $exit, $output);
        $this->assertSame("6\n", $output);
    }

    public function testScriptExitsTwoWhenTheBaselineIsMissing(): void
    {
        [$exit, $output] = $this->runScript(['count', 'tests/fixtures/baseline/nope.neon']);

        $this->assertSame(2, $exit, $output);
        $this->assertStringContainsString('baseline file not found', $output);
    }

    public function testCheckPassesWhenTheCountIsWithinTheLimit(): void
    {
        [$exit, $output] = $this->runScript(['check', '--max=6', 'tests/fixtures/baseline/phpstan-baseline.neon']);

        $this->assertSame(0, $exit, $output);
    }

    public function testCheckFailsWhenTheCountExceedsTheLimit(): void
    {
        [$exit, $output] = $this->runScript(['check', '--max=5', 'tests/fixtures/baseline/phpstan-baseline.neon']);

        $this->assertSame(1, $exit, $output);
        $this->assertSame("baseline grew: 6 errors, the most allowed is 5\n", $output);
    }

    public function testCountAtRefReadsTheBaselineAsItWasCommitted(): void
    {
        $dir = $this->createRepo();
        $sha = $this->commitFile($dir, 'phpstan-baseline.neon', $this->neonBaseline(2));

        // The working file grows past the committed one.
        $this->assertNotFalse(file_put_contents($dir . '/phpstan-baseline.neon', $this->neonBaseline(5)));

        $previous = getcwd();
        $this->assertIsString($previous);
        chdir($dir);

        try {
            $this->assertSame(2, Baseline::countAtRef('phpstan-baseline.neon', $sha));
            $this->assertSame(5, Baseline::countFile('phpstan-baseline.neon'));
        } finally {
            chdir($previous);
        }
    }

    public function testCheckAgainstExitsZeroWhenTheBaselineDidNotGrow(): void
    {
        $dir = $this->createRepo();
        $sha = $this->commitFile($dir, 'phpstan-baseline.neon', $this->neonBaseline(2));

        // The working file is different but still two errors, so it did not grow.
        $rewritten = "parameters:\n    ignoreErrors:\n        - message: '#^Pinstripe test error one$#'\n"
            . "        - message: '#^Pinstripe test error two$#'\n";
        $this->assertNotFalse(file_put_contents($dir . '/phpstan-baseline.neon', $rewritten));

        [$exit, $output] = $this->runScript(['check', '--against=' . $sha, 'phpstan-baseline.neon'], $dir);

        $this->assertSame(0, $exit, $output);
    }

    public function testCheckAgainstExitsOneWhenTheBaselineGrewPastTheRef(): void
    {
        $dir = $this->createRepo();
        $sha = $this->commitFile($dir, 'phpstan-baseline.neon', $this->neonBaseline(2));
        $this->assertNotFalse(file_put_contents($dir . '/phpstan-baseline.neon', $this->neonBaseline(5)));

        [$exit, $output] = $this->runScript(['check', '--against=' . $sha, 'phpstan-baseline.neon'], $dir);

        $this->assertSame(1, $exit, $output);
        $this->assertSame("baseline grew: 5 errors, the most allowed is 2\n", $output);
    }

    public function testCheckAgainstExitsTwoWhenTheRefIsUnknown(): void
    {
        $dir = $this->createRepo();
        $this->commitFile($dir, 'phpstan-baseline.neon', $this->neonBaseline(2));

        [$exit, $output] = $this->runScript(['check', '--against=no-such-ref', 'phpstan-baseline.neon'], $dir);

        $this->assertSame(2, $exit, $output);
        $this->assertStringContainsString('could not read phpstan-baseline.neon at no-such-ref', $output);
    }
}
