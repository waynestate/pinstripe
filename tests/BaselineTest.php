<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use PHPUnit\Framework\TestCase;
use WayneState\Pinstripe\Baseline;
use WayneState\Pinstripe\BaselineNotFoundException;

/**
 * Counts PHPStan baselines in both formats, and drives the pinstripe-baseline
 * script end to end for its exit codes.
 *
 * Commands use the array form with proc_open and no shell, so the tests
 * behave the same on Windows, macOS and Linux.
 */
final class BaselineTest extends TestCase
{
    /**
     * Run a command in the package root and return [exit code, combined output].
     *
     * @param list<string> $command
     *
     * @return array{0: int, 1: string}
     */
    private function runCommand(array $command): array
    {
        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $this->root()
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
     * Run the pinstripe-baseline script with the given arguments.
     *
     * @param list<string> $arguments
     *
     * @return array{0: int, 1: string}
     */
    private function runScript(array $arguments): array
    {
        return $this->runCommand(array_merge([PHP_BINARY, $this->root() . '/bin/pinstripe-baseline'], $arguments));
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
}
