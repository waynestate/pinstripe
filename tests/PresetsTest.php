<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Runs the real Pint, PHPStan and Rector binaries against the fixtures.
 *
 * Commands use the array form with proc_open and no shell, so the tests
 * behave the same on Windows, macOS and Linux.
 */
final class PresetsTest extends TestCase
{
    private static ?string $laravelAppDirectory = null;

    public static function tearDownAfterClass(): void
    {
        if (self::$laravelAppDirectory !== null) {
            self::removeTemporaryFixture(self::$laravelAppDirectory);
            self::$laravelAppDirectory = null;
        }

        parent::tearDownAfterClass();
    }

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
        // The package's own vendor carries Larastan (require-dev), which the
        // extension installer loads for every PHPStan run. Larastan boots from
        // a Laravel app in the working directory, so run the raw preset from
        // the laravel fixture, which carries a minimal bootstrap/app.php.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/raw.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-clean/src',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);
    }

    public function testPhpStanRawConfigReportsTheNullBug(): void
    {
        // See testPhpStanRawConfigAcceptsTheCleanFixture for why the raw
        // preset runs from the laravel fixture's working directory.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/raw.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-nullbug/src',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString('string|null', $output);
    }

    public function testPhpStanRawConfigReportsTheDeprecatedCall(): void
    {
        // See testPhpStanRawConfigAcceptsTheCleanFixture for why the raw
        // preset runs from the laravel fixture's working directory.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/raw.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-deprecated/src',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString(
            'Call to deprecated method greet() of class Pinstripe\\Fixtures\\RawDeprecated\\LegacyGreeter',
            $output
        );
    }

    public function testRectorPresetReportsChangesOnTheOldFixture(): void
    {
        [$exit, $output] = $this->runRectorDryRun('rector-old');

        $this->assertNotSame(0, $exit, $output);
    }

    public function testRectorPresetAcceptsTheCleanFixture(): void
    {
        [$exit, $output] = $this->runRectorDryRun('rector-clean');

        $this->assertSame(0, $exit, $output);
    }

    /**
     * Run `rector process --dry-run` on a fixture, with the fixture as the
     * working directory so Rector reads the PHP floor from the fixture's own
     * composer.json, exactly as it would from an app's.
     *
     * @return array{0: int, 1: string}
     */
    private function runRectorDryRun(string $fixture): array
    {
        return $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/rector',
            'process',
            '--dry-run',
            '-c',
            'rector.php',
        ], $this->root() . '/tests/fixtures/' . $fixture);
    }

    public function testLaravelPresetLoadsLarastanThroughTheExtensionInstaller(): void
    {
        $fixture = $this->temporaryConsumerFixture('laravel-consumer');

        try {
            // `update` (not `install`) because the fixture's composer.lock is
            // gitignored, so a fresh checkout has no lock to install from.
            [$installExit, $installOutput] = $this->runInDir(
                array_merge($this->composerCommand(), ['update', '--no-interaction', '--no-progress']),
                $fixture
            );

            $this->assertSame(0, $installExit, $installOutput);

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
            $extensions = (string) file_get_contents($generated);
            $this->assertStringContainsString('larastan/larastan', $extensions);
            $this->assertStringContainsString('phpstan/phpstan-deprecation-rules', $extensions);
        } finally {
            self::removeTemporaryFixture($fixture);
        }
    }

    private function temporaryConsumerFixture(string $name): string
    {
        // Composer cannot mirror a path repository inside its own source.
        // Start outside the repo, without cached dependencies or a lockfile.
        $fixture = sys_get_temp_dir() . '/pinstripe-' . $name . '-' . bin2hex(random_bytes(8));
        $this->assertTrue(mkdir($fixture));

        try {
            $this->copyConsumerFixture($name, $fixture);
            $composerFile = $fixture . '/composer.json';
            $composer = json_decode((string) file_get_contents($composerFile), true, 512, JSON_THROW_ON_ERROR);
            $composer['repositories'][0]['url'] = $this->root();
            $this->assertNotFalse(file_put_contents(
                $composerFile,
                json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n"
            ));
        } catch (\Throwable $exception) {
            self::removeTemporaryFixture($fixture);

            throw $exception;
        }

        return $fixture;
    }

    private function copyConsumerFixture(string $name, string $destination): void
    {
        $source = $this->root() . '/tests/fixtures/' . $name;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($source, \FilesystemIterator::SKIP_DOTS),
                static fn (\SplFileInfo $file): bool => !in_array($file->getFilename(), ['vendor', 'composer.lock'], true)
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $target = $destination . substr($file->getPathname(), strlen($source));
            if ($file->isDir()) {
                $this->assertTrue(mkdir($target));
            } else {
                $this->assertTrue(copy($file->getPathname(), $target));
            }
        }
    }

    private static function removeTemporaryFixture(string $fixture): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fixture, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir() && !$file->isLink()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($fixture);
    }

    public function testPintLaravelPresetAcceptsTheLaravelAppFixture(): void
    {
        $fixture = $this->laravelAppFixture();

        // `bootstrap/app.php` is passed as a file, not the `bootstrap`
        // directory: Laravel writes generated caches into bootstrap/cache at
        // runtime, and those are not the app's code.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'vendor/waynestate/pinstripe/pint/laravel.json',
            'app',
            'routes',
            'bootstrap/app.php',
        ], $fixture);

        $this->assertSame(0, $exit, $output);
    }

    public function testPhpStanLaravelConfigAcceptsTheLaravelAppFixture(): void
    {
        $fixture = $this->laravelAppFixture();

        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            'vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            '--error-format=raw',
            'app',
            'bootstrap',
            'routes',
        ], $fixture);

        $this->assertSame(0, $exit, $output);

        $generated = $fixture . '/vendor/phpstan/extension-installer/src/GeneratedConfig.php';
        $this->assertFileExists($generated);
        $this->assertStringContainsString('larastan/larastan', (string) file_get_contents($generated));
    }

    public function testPhpStanLaravelConfigReportsTheUndefinedEloquentProperty(): void
    {
        $fixture = $this->laravelAppFixture();

        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            'vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            '--error-format=raw',
            'bad',
        ], $fixture);

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString('Access to an undefined property App\Models\Greeting::$audience', $output);
    }

    public function testLaravelPresetReportsTheUndefinedEloquentProperty(): void
    {
        $fixture = $this->root() . '/tests/fixtures/laravel';

        // The fixture has no vendor of its own: it runs the package's own
        // PHPStan binary, so Larastan comes from the package's require-dev.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            '--error-format=raw',
            'src/BrokenGreeting.php',
        ], $fixture);

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString('Access to an undefined property Pinstripe\Fixtures\Laravel\Greeting::$audience', $output);
    }

    public function testLaravelPresetAcceptsTheCleanTwin(): void
    {
        $fixture = $this->root() . '/tests/fixtures/laravel';

        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            '--error-format=raw',
            'src/CleanGreeting.php',
        ], $fixture);

        $this->assertSame(0, $exit, $output);
    }

    /**
     * A temporary Laravel app fixture, installed on first use.
     *
     * `update` (not `install`) because the fixture's composer.lock is
     * gitignored, so a fresh checkout has no lock to install from. The
     * vendor directory is shared by this class's tests and removed by
     * tearDownAfterClass, so each suite run starts from a fresh install.
     */
    private function laravelAppFixture(): string
    {
        self::$laravelAppDirectory ??= $this->temporaryConsumerFixture('laravel-app');
        $fixture = self::$laravelAppDirectory;

        if (!is_file($fixture . '/vendor/autoload.php')) {
            [$installExit, $installOutput] = $this->runInDir(
                array_merge($this->composerCommand(), ['update', '--no-interaction', '--no-progress']),
                $fixture
            );

            $this->assertSame(0, $installExit, $installOutput);
        }

        return $fixture;
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
