<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Runs the real Pint and PHPStan binaries against the fixtures.
 *
 * Commands use the array form with proc_open and no shell, so the tests
 * behave the same on Windows, macOS and Linux.
 */
final class PresetsTest extends TestCase
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

    public function testPintPerPresetAcceptsTheCleanFixture(): void
    {
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'pint/per.json',
            'tests/fixtures/raw-clean',
        ]);

        $this->assertSame(0, $exit, $output);
    }

    public function testPintPerPresetRejectsTheMessyFixture(): void
    {
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'pint/per.json',
            'tests/fixtures/raw-messy',
        ]);

        $this->assertNotSame(0, $exit, $output);
    }

    public function testPhpStanRawConfigAcceptsTheCleanFixture(): void
    {
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan/raw.neon',
            '--no-progress',
            '--error-format=raw',
            'tests/fixtures/raw-clean/src',
        ]);

        $this->assertSame(0, $exit, $output);
    }

    public function testPhpStanRawConfigReportsTheNullBug(): void
    {
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan/raw.neon',
            '--no-progress',
            '--error-format=raw',
            'tests/fixtures/raw-nullbug/src',
        ]);

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString('string|null', $output);
    }

    public function testPintConfigsDeclareTheirPresets(): void
    {
        $root = $this->root();

        $per = json_decode((string) file_get_contents($root . '/pint/per.json'), true, 512, JSON_THROW_ON_ERROR);
        $laravel = json_decode((string) file_get_contents($root . '/pint/laravel.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('per', $per['preset']);
        $this->assertSame('laravel', $laravel['preset']);
    }
}
