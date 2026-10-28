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
        return $this->runInDir($command, $this->root());
    }

    /**
     * Run a command in a directory and return [exit code, combined output].
     *
     * @param list<string> $command
     *
     * @return array{0: int, 1: string}
     */
    private function runInDir(array $command, string $dir): array
    {
        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            $dir
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

    /**
     * A command that runs Composer without a shell, on every OS.
     *
     * Linux and macOS ship `composer` as a plain executable. Windows ships a
     * .bat wrapper, which proc_open cannot run, so there we run the phar with
     * PHP, looking it up in COMPOSER_HOME.
     *
     * @return list<string>
     */
    private function composerCommand(): array
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            return ['composer'];
        }

        $home = getenv('COMPOSER_HOME');
        if ($home === false) {
            $appData = getenv('APPDATA');
            $home = $appData === false ? null : $appData . DIRECTORY_SEPARATOR . 'Composer';
        }

        if ($home !== null && is_file($home . DIRECTORY_SEPARATOR . 'composer.phar')) {
            return [PHP_BINARY, $home . DIRECTORY_SEPARATOR . 'composer.phar'];
        }

        return ['composer'];
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

    public function testLaravelPresetLoadsLarastanThroughTheExtensionInstaller(): void
    {
        $fixture = $this->root() . '/tests/fixtures/laravel-consumer';

        if (!is_file($fixture . '/vendor/autoload.php')) {
            // `update` (not `install`) because the fixture's composer.lock is
            // gitignored, so a fresh checkout has no lock to install from.
            [$installExit, $installOutput] = $this->runInDir(
                array_merge($this->composerCommand(), ['update', '--no-interaction', '--no-progress']),
                $fixture
            );

            $this->assertSame(0, $installExit, $installOutput);
        }

        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            'vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            '--error-format=raw',
            'src',
        ], $fixture);

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString('string|null', $output);
        $this->assertStringNotContainsString('Function config not found', $output);

        $generated = $fixture . '/vendor/phpstan/extension-installer/src/GeneratedConfig.php';
        $this->assertFileExists($generated);
        $this->assertStringContainsString('larastan/larastan', (string) file_get_contents($generated));
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
