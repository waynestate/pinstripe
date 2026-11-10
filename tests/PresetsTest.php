<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
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

    private static ?string $laravelVueDirectory = null;

    private static ?string $laravelInertiaDirectory = null;

    private static ?string $rawVueDirectory = null;

    public static function tearDownAfterClass(): void
    {
        if (self::$laravelAppDirectory !== null) {
            self::removeTemporaryFixture(self::$laravelAppDirectory);
            self::$laravelAppDirectory = null;
        }

        if (self::$laravelVueDirectory !== null) {
            self::removeTemporaryFixture(self::$laravelVueDirectory);
            self::$laravelVueDirectory = null;
        }

        if (self::$laravelInertiaDirectory !== null) {
            self::removeTemporaryFixture(self::$laravelInertiaDirectory);
            self::$laravelInertiaDirectory = null;
        }

        if (self::$rawVueDirectory !== null) {
            self::removeTemporaryFixture(self::$rawVueDirectory);
            self::$rawVueDirectory = null;
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

    public function testPintPerPresetAcceptsThePerCleanFixture(): void
    {
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'pint/per.json',
            'tests/fixtures/per/clean',
        ]);

        $this->assertSame(0, $exit, $output);
    }

    public function testPintPerPresetRejectsThePerMessyFixture(): void
    {
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'pint/per.json',
            'tests/fixtures/per/messy',
        ]);

        $this->assertNotSame(0, $exit, $output);
    }

    public function testPintGitlabFormatReportsTheMessyFixtureAsCodeQualityJson(): void
    {
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'pint/per.json',
            '--format=gitlab',
            'tests/fixtures/per/messy',
        ]);

        $this->assertNotSame(0, $exit, $output);

        $start = strpos($output, '[');
        $end = strrpos($output, ']');
        $this->assertNotFalse($start, $output);
        $this->assertNotFalse($end, $output);

        $json = substr($output, $start, $end - $start + 1);
        $issues = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($issues);
        $this->assertNotEmpty($issues);

        foreach ($issues as $issue) {
            $this->assertIsArray($issue);
            $this->assertArrayHasKey('check_name', $issue);
            $this->assertArrayHasKey('description', $issue);
            $this->assertArrayHasKey('fingerprint', $issue);
            $this->assertArrayHasKey('severity', $issue);
            $this->assertArrayHasKey('location', $issue);
            $this->assertIsArray($issue['location']);
            $this->assertArrayHasKey('path', $issue['location']);
            $this->assertStringStartsWith('PHP-CS-Fixer.', $issue['check_name']);
            $this->assertStringEndsWith('Greeter.php', $issue['location']['path']);
        }
    }

    public function testPintGitlabFormatIsAnEmptyArrayForTheCleanFixture(): void
    {
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'pint/per.json',
            '--format=gitlab',
            'tests/fixtures/per/clean',
        ]);

        $this->assertSame(0, $exit, $output);

        $start = strpos($output, '[');
        $end = strrpos($output, ']');
        $this->assertNotFalse($start, $output);
        $this->assertNotFalse($end, $output);

        $json = substr($output, $start, $end - $start + 1);
        $this->assertSame('[]', trim($json));
    }

    public function testPintPerStrictPresetAcceptsTheStrictTypesCleanFixture(): void
    {
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'pint/per-strict.json',
            'tests/fixtures/strict-types/clean',
        ]);

        $this->assertSame(0, $exit, $output);
    }

    public function testPintPerStrictPresetRejectsTheStrictTypesMissingFixture(): void
    {
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'pint/per-strict.json',
            'tests/fixtures/strict-types/missing',
        ]);

        $this->assertNotSame(0, $exit, $output);
    }

    public function testPintLaravelPresetRejectsTheMessyFixture(): void
    {
        // The other half of testPintLaravelPresetAcceptsTheLaravelAppFixture:
        // long array syntax and a brace on the wrong line fail Laravel's
        // style, so the preset is proven to catch something, not only to pass.
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'pint/laravel.json',
            'tests/fixtures/raw-messy',
        ]);

        $this->assertNotSame(0, $exit, $output);
    }

    public function testPintLaravelStrictPresetAcceptsTheStrictTypesLaravelCleanFixture(): void
    {
        // Its own clean twin, because the PER one fails Laravel's style on
        // concatenation spacing (`'a' . $b`), which is not what this proves:
        // a file in Laravel style that declares strict types passes.
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'pint/laravel-strict.json',
            'tests/fixtures/strict-types/laravel-clean',
        ]);

        $this->assertSame(0, $exit, $output);
    }

    public function testPintLaravelStrictPresetRejectsTheStrictTypesMissingFixture(): void
    {
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'pint/laravel-strict.json',
            'tests/fixtures/strict-types/missing',
        ]);

        $this->assertNotSame(0, $exit, $output);
    }

    public function testPintPlainPerPresetStillAcceptsTheStrictTypesMissingFixture(): void
    {
        [$exit, $output] = $this->runCommand([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'pint/per.json',
            'tests/fixtures/strict-types/missing',
        ]);

        $this->assertSame(0, $exit, $output);
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

    public function testPhpStanGitlabFormatReportsTheNullBugAsCodeQualityJson(): void
    {
        // The README's analyse:gitlab script: PHPStan's own gitlab error
        // format, read by GitLab as a Code Quality report. Same working
        // directory as the other raw-preset runs (see above).
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/raw.neon',
            '--no-progress',
            '--error-format=gitlab',
            $this->root() . '/tests/fixtures/raw-nullbug/src',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);

        $start = strpos($output, '[');
        $end = strrpos($output, ']');
        $this->assertNotFalse($start, $output);
        $this->assertNotFalse($end, $output);

        $issues = json_decode(substr($output, $start, $end - $start + 1), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($issues);
        $this->assertNotEmpty($issues);

        foreach ($issues as $issue) {
            $this->assertIsArray($issue);
            $this->assertArrayHasKey('description', $issue);
            $this->assertArrayHasKey('fingerprint', $issue);
            $this->assertArrayHasKey('severity', $issue);
            $this->assertArrayHasKey('location', $issue);
            $this->assertIsArray($issue['location']);
            $this->assertArrayHasKey('path', $issue['location']);
            $this->assertStringEndsWith('.php', $issue['location']['path']);
            $this->assertIsArray($issue['location']['lines'] ?? null, $output);
            $this->assertIsInt($issue['location']['lines']['begin'] ?? null, $output);
            $this->assertArrayHasKey('check_name', $issue);
        }

        $this->assertStringContainsString('string|null', implode("\n", array_column($issues, 'description')));
    }

    public function testPhpStanGitlabFormatIsAnEmptyArrayForTheCleanFixture(): void
    {
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/raw.neon',
            '--no-progress',
            '--error-format=gitlab',
            $this->root() . '/tests/fixtures/raw-clean/src',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);

        $start = strpos($output, '[');
        $end = strrpos($output, ']');
        $this->assertNotFalse($start, $output);
        $this->assertNotFalse($end, $output);
        $this->assertSame('[]', trim(substr($output, $start, $end - $start + 1)));
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

    public function testPhpStanRawConfigReportsAnIgnoreWithoutAComment(): void
    {
        // The base cut sets reportIgnoresWithoutComments, so an
        // @phpstan-ignore with no comment in parentheses fails the run.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/raw.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-ignores/src/NoComment.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString('Ignore with identifier return.type has no comment.', $output);
    }

    public function testPhpStanRawConfigReportsAnIgnoreThatMatchesNothing(): void
    {
        // The base cut sets reportUnmatchedIgnoredErrors, so an
        // @phpstan-ignore whose identifier matches no error on that line
        // fails the run.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/raw.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-ignores/src/Unmatched.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString('No error with identifier return.type is reported on line', $output);
    }

    public function testPhpStanRawConfigAcceptsAnIgnoreWithAComment(): void
    {
        // The same ignore with a comment in parentheses is accepted: the
        // error stays suppressed and the run is clean.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/raw.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-ignores/src/Commented.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);
    }

    public function testPhpStanRawConfigAcceptsTheLooseComparisonFixture(): void
    {
        // The base cut leaves `==` alone; only the strict preset calls it out.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/raw.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-strict/src',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);
    }

    public function testPhpStanStrictConfigAcceptsTheCleanFixture(): void
    {
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/strict.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-clean/src',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);
    }

    public function testPhpStanStrictConfigReportsTheLooseComparison(): void
    {
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/strict.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-strict/src',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString('Loose comparison via "==" is not allowed.', $output);
    }

    public function testPhpStanRawConfigAcceptsTheVarianceFixture(): void
    {
        // The base cut pins reportStaticMethodSignatures and
        // reportMaybesInMethodSignatures to false, so the phpdoc-aware
        // signature check (on from level 3 up) skips the static override
        // in the fixture and leaves it alone.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/raw.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-variance/src',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);
    }

    public function testPhpStanStrictConfigReportsTheVarianceFixture(): void
    {
        // The strict cut keeps reportStaticMethodSignatures on, so the
        // same fixture is called out for narrowing the static override's
        // parameter from string to int.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/strict.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-variance/src',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString(
            'Parameter #1 $item (int) of method Pinstripe\\Fixtures\\RawVariance\\BorrowedShirt::put() should be compatible with parameter $item (string) of method Pinstripe\\Fixtures\\RawVariance\\Shirt::put()',
            $output
        );
    }

    public function testPhpStanRawConfigPinsTheLeakedSignatureParamsOff(): void
    {
        // phpstan/phpstan-strict-rules forces both signature parameters to
        // true for every run it is installed for. The probe config reads
        // the effective values out of its unmatched-ignore error (Nette
        // renders a false parameter as an empty string and true as 1), so
        // PROBE_M_F_S_F proves the base cut keeps both pinned off.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/tests/fixtures/raw-variance/raw-probe.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-variance/src',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString('PROBE_M_F_S_F_D_E1_N1_I1_P_V_X1_K', $output);
    }

    public function testPhpStanStrictConfigPinsTheLeakedSignatureParamsOn(): void
    {
        // Counterpart of the raw probe: the strict cut keeps both
        // parameters on, so the marker renders PROBE_M_F1_S_F1.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/tests/fixtures/raw-variance/strict-probe.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-variance/src',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString('PROBE_M_F1_S_F1_D1_E1_N1_I1_P1_V1_X1_K1', $output);
    }

    /**
     * The banned calls in phpstan/disallowed.neon: identifier => the start
     * of the message and the OWASP Top 10:2025 tag it must carry.
     */
    private const BANNED_CALLS = [
        'pinstripe.banned.eval' => ['Calling eval() is forbidden, it runs a string as PHP code', 'OWASP A05:2025 Injection'],
        'pinstripe.banned.extract' => ['Calling extract() is forbidden, it turns array keys into local variables', 'OWASP A05:2025 Injection'],
        'pinstripe.banned.varDump' => ['Calling var_dump() is forbidden, debug output left in', 'OWASP A02:2025 Security Misconfiguration'],
        'pinstripe.banned.dd' => ['Calling dd() is forbidden, a dd() left in', 'OWASP A02:2025 Security Misconfiguration'],
        'pinstripe.banned.dump' => ['Calling dump() is forbidden, a dump() left in', 'OWASP A02:2025 Security Misconfiguration'],
    ];

    /**
     * @return array<string, array{0: string}>
     */
    public static function bannedCallConfigs(): array
    {
        return ['raw cut' => ['raw.neon'], 'strict cut' => ['strict.neon']];
    }

    /**
     * Run PHPStan with JSON output and return [exit code, raw output, the
     * reported messages as [identifier, message, line] triples].
     *
     * @param list<string> $command
     *
     * @return array{0: int, 1: string, 2: list<array{0: string, 1: string, 2: int}>}
     */
    private function runPhpStanJson(array $command, string $dir): array
    {
        [$exit, $output] = $this->runInDir(array_merge($command, ['--error-format=json']), $dir);

        $start = strpos($output, '{');
        $this->assertNotFalse($start, $output);
        $report = json_decode(substr($output, $start), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($report, $output);
        $this->assertSame([], $report['errors'], $output);

        $messages = [];
        foreach ($report['files'] as $file) {
            foreach ($file['messages'] as $message) {
                $messages[] = [(string) ($message['identifier'] ?? ''), (string) $message['message'], (int) $message['line']];
            }
        }

        return [$exit, $output, $messages];
    }

    #[DataProvider('bannedCallConfigs')]
    public function testPhpStanReportsEveryBannedCall(string $config): void
    {
        // Every cut includes disallowed.neon through base.neon, so the raw
        // and strict cuts report each banned call, and nothing else.
        [$exit, $output, $messages] = $this->runPhpStanJson([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/' . $config,
            '--no-progress',
            $this->root() . '/tests/fixtures/raw-disallowed/src/Leftovers.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertSame(array_keys(self::BANNED_CALLS), array_column($messages, 0), $output);

        foreach ($messages as [$identifier, $message]) {
            [$start, $owasp] = self::BANNED_CALLS[$identifier];
            $this->assertStringStartsWith($start, $message);
            $this->assertStringEndsWith('(' . $owasp . ').', $message);
            $this->assertStringContainsString('; ', $message, 'a ban message says what to use instead after the reason');
        }
    }

    #[DataProvider('bannedCallConfigs')]
    public function testPhpStanAcceptsMethodsThatShareTheBannedNames(string $config): void
    {
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/' . $config,
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-disallowed/src/LookAlikes.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);
    }

    #[DataProvider('bannedCallConfigs')]
    public function testPhpStanReportsTlsChecksTurnedOffInRawPhp(string $config): void
    {
        // curl_setopt() is Pinstripe's own rule (two parameters at once);
        // curl_setopt_array() and stream_context_create() are array shapes
        // in disallowed.neon. All of them read alike.
        [$exit, $output, $messages] = $this->runPhpStanJson([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/' . $config,
            '--no-progress',
            $this->root() . '/tests/fixtures/raw-disallowed/src/InsecureTransport.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertSame(
            [
                ['pinstripe.banned.curlVerifyOff', 'curl_setopt()', 18],
                ['pinstripe.banned.curlVerifyOff', 'curl_setopt()', 23],
                ['pinstripe.banned.curlVerifyOff', 'curl_setopt()', 28],
                ['pinstripe.banned.curlVerifyOff', 'curl_setopt_array()', 33],
                ['pinstripe.banned.curlVerifyOff', 'curl_setopt_array()', 38],
                ['pinstripe.banned.streamVerifyOff', 'stream_context_create()', 46],
                ['pinstripe.banned.streamVerifyOff', 'stream_context_create()', 54],
                ['pinstripe.banned.streamVerifyOff', 'stream_context_create()', 62],
            ],
            array_map(
                static fn (array $message): array => [$message[0], (string) preg_replace('/^Calling (\S+) is forbidden.*$/s', '$1', $message[1]), $message[2]],
                $messages
            ),
            $output
        );

        foreach ($messages as [, $message]) {
            $this->assertMatchesRegularExpression('/^Calling \S+ is forbidden, it (turns off TLS|accepts self-signed)/', $message);
            $this->assertStringContainsString('; ', $message, 'a ban message says what to use instead after the reason');
            $this->assertStringEndsWith('(OWASP A02:2025 Security Misconfiguration).', $message);
        }
    }

    #[DataProvider('bannedCallConfigs')]
    public function testPhpStanAcceptsTlsChecksLeftOnInRawPhp(string $config): void
    {
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/' . $config,
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-disallowed/src/SecureTransport.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);
    }

    #[DataProvider('bannedCallConfigs')]
    public function testPhpStanReportsSqlJoinedFromStringsInRawPhp(string $config): void
    {
        // PDO, mysqli and oci8 with a variable joined into the SQL: by
        // concatenation, interpolation, sprintf() and an int. PHPStan's own
        // literal-string type does the telling; disallowed.neon asks for it.
        // The ociparse() alias is also reported as deprecated by the
        // deprecation rules on every PHP version Pinstripe supports, and the
        // preset keeps that report, so expect it always.
        $expected = [
            ['pinstripe.banned.joinedSql', 'PDO::query()', 22],
            ['pinstripe.banned.joinedSql', 'PDO::prepare()', 23],
            ['pinstripe.banned.joinedSql', 'PDO::exec()', 24],
            ['pinstripe.banned.joinedSql', 'PDO::query()', 25],
            ['pinstripe.banned.joinedSql', 'mysqli::query()', 26],
            ['pinstripe.banned.joinedSql', 'mysqli::prepare()', 27],
            ['pinstripe.banned.joinedSql', 'mysqli_query()', 28],
            ['pinstripe.banned.joinedSql', 'oci_parse()', 29],
            ['pinstripe.banned.joinedSql', 'ociparse()', 30],
        ];
        $expected[] = ['function.deprecated', 'ociparse()', 30];

        [$exit, $output, $messages] = $this->runPhpStanJson([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/' . $config,
            '--no-progress',
            $this->root() . '/tests/fixtures/raw-disallowed/src/JoinedSql.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $reported = array_map(
            static function (array $message): array {
                $name = $message[0] === 'function.deprecated'
                    ? (string) preg_replace('/^Call to deprecated function (\w+)\(.*$/s', '$1()', $message[1])
                    : (string) preg_replace('/^Calling (\S+) is forbidden.*$/s', '$1', $message[1]);

                return [$message[0], $name, $message[2]];
            },
            $messages
        );
        sort($expected);
        sort($reported);
        $this->assertSame($expected, $reported, $output);

        foreach ($messages as [$identifier, $message]) {
            if ($identifier === 'function.deprecated') {
                $this->assertStringContainsString('Call to deprecated function ociparse()', $message);
                continue;
            }
            $this->assertStringContainsString('is forbidden, it runs SQL joined from strings', $message);
            $this->assertStringContainsString('; ', $message, 'a ban message says what to use instead after the reason');
            $this->assertStringEndsWith('(OWASP A05:2025 Injection).', $message);
        }
    }

    #[DataProvider('bannedCallConfigs')]
    public function testPhpStanAcceptsLiteralSqlWithBoundValuesInRawPhp(string $config): void
    {
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/' . $config,
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-disallowed/src/BoundSql.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);
    }

    #[DataProvider('bannedCallConfigs')]
    public function testPhpStanAcceptsSqlReadFromFilesThroughATypedWrapper(string $config): void
    {
        // The fix the Banned calls page gives for SQL kept in .sql files: a
        // reader typed @return literal-string (one reasoned ignore) and a
        // wrapper whose SQL parameter is literal-string. No baseline needed.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/' . $config,
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-disallowed/src/SqlFiles.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);
    }

    #[DataProvider('bannedCallConfigs')]
    public function testPhpStanReportsSqlJoinedThroughAWrapperAtTheCaller(string $config): void
    {
        // A literal-string wrapper moves the report to each joined caller
        // (argument.type). A plain-string wrapper is reported once, inside,
        // and its joined caller (line 23) is not reported at all: that is why
        // the docs say to type a wrapper, never to baseline it.
        [$exit, $output, $messages] = $this->runPhpStanJson([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/' . $config,
            '--no-progress',
            $this->root() . '/tests/fixtures/raw-disallowed/src/SqlFiles.php',
            $this->root() . '/tests/fixtures/raw-disallowed/src/JoinedThroughAWrapper.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertSame(
            [
                ['argument.type', 21],
                ['argument.type', 22],
                ['pinstripe.banned.joinedSql', 40],
            ],
            array_map(static fn (array $message): array => [$message[0], $message[2]], $messages),
            $output
        );

        foreach ($messages as [$identifier, $message]) {
            if ($identifier === 'argument.type') {
                $this->assertStringContainsString('expects literal-string', $message);
            } else {
                $this->assertStringContainsString('is forbidden, it runs SQL joined from strings', $message);
            }
        }
    }

    public function testLaravelPresetReportsSqlJoinedFromStrings(): void
    {
        // Pinstripe bans the connection's calls; the builder's raw methods
        // are typed literal-string by Laravel, so PHPStan reports those as
        // argument.type. Both halves are asserted, so a Laravel release that
        // drops its literal-string types fails here instead of going quiet.
        [$exit, $output, $messages] = $this->runPhpStanJson([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            'src/JoinedQueries.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertSame(
            [
                ['pinstripe.banned.joinedSql', 27],
                ['pinstripe.banned.joinedSql', 28],
                ['pinstripe.banned.joinedSql', 29],
                ['pinstripe.banned.joinedSql', 30],
                ['pinstripe.banned.joinedSql', 31],
                ['pinstripe.banned.joinedSql', 32],
                ['argument.type', 34],
                ['argument.type', 35],
                ['argument.type', 36],
            ],
            array_map(static fn (array $message): array => [$message[0], $message[2]], $messages),
            $output
        );

        foreach ($messages as [$identifier, $message]) {
            if ($identifier === 'pinstripe.banned.joinedSql') {
                $this->assertStringContainsString('is forbidden, it runs SQL joined from strings', $message);
                $this->assertStringEndsWith('(OWASP A05:2025 Injection).', $message);
            } else {
                $this->assertStringContainsString('literal-string', $message);
            }
        }
    }

    public function testLaravelPresetAcceptsLiteralSqlWithBindings(): void
    {
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            '--error-format=raw',
            'src/BoundQueries.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);
    }

    public function testPhpStanRawConfigAcceptsABannedCallKeptWithAReason(): void
    {
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            $this->root() . '/phpstan/raw.neon',
            '--no-progress',
            '--error-format=raw',
            $this->root() . '/tests/fixtures/raw-disallowed/src/Excused.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);
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

    public function testRectorPresetAppliesTheComposerBasedLaravelSet(): void
    {
        $fixture = $this->temporaryConsumerFixture('laravel-consumer');

        try {
            // Same install story as the Larastan test: the fixture's
            // composer.lock is gitignored, so a fresh checkout needs an
            // `update`. The fixture carries rector/rector and
            // driftingly/rector-laravel as its own dev dependencies, exactly
            // as a consuming app would, so the Laravel sets are autoloadable
            // when its rector binary runs.
            [$installExit, $installOutput] = $this->runInDir(
                array_merge($this->composerCommand(), ['update', '--no-interaction', '--no-progress']),
                $fixture
            );

            $this->assertSame(0, $installExit, $installOutput);

            // A dry run exits non-zero when it proposes changes.
            [$exit, $output] = $this->runInDir([
                PHP_BINARY,
                'vendor/bin/rector',
                'process',
                '--dry-run',
                '-c',
                'rector.php',
            ], $fixture);

            $this->assertNotSame(0, $exit, $output);
            $this->assertStringContainsString('$request->input(', $output);
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

    public function testPhpStanLaravelConfigReportsALeftoverDdInTheLaravelAppFixture(): void
    {
        // An app that installs Pinstripe from Composer gets the bans with
        // no new config line: spaze/phpstan-disallowed-calls arrives through
        // Pinstripe's `require` and registers itself through the extension
        // installer, and laravel.neon pulls in disallowed.neon.
        $fixture = $this->laravelAppFixture();

        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            'vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            '--error-format=raw',
            'banned',
        ], $fixture);

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString('Calling dd() is forbidden, a dd() left in', $output);

        $generated = $fixture . '/vendor/phpstan/extension-installer/src/GeneratedConfig.php';
        $this->assertFileExists($generated);
        $this->assertStringContainsString('spaze/phpstan-disallowed-calls', (string) file_get_contents($generated));
    }

    public function testPhpStanLaravelConfigReportsCurlVerifyOffInTheLaravelAppFixture(): void
    {
        // The curl_setopt() ban is Pinstripe's own rule class, registered in
        // disallowed.neon. An app that installs Pinstripe from Composer must
        // load it from vendor/ through its own autoloader.
        $fixture = $this->laravelAppFixture();

        [$exit, $output, $messages] = $this->runPhpStanJson([
            PHP_BINARY,
            'vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            'banned/InsecureCurl.php',
        ], $fixture);

        $this->assertNotSame(0, $exit, $output);
        $this->assertSame([['pinstripe.banned.curlVerifyOff', 19]], array_map(
            static fn (array $message): array => [$message[0], $message[2]],
            $messages
        ), $output);
    }

    public function testPintLaravelPresetAcceptsTheLaravelVueFixture(): void
    {
        $fixture = $this->laravelVueFixture();

        // The Vue side of the fixture lives in resources/js and is covered by
        // the ESLint and Prettier tests; Pint only reads the PHP paths.
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

    public function testPhpStanLaravelConfigAcceptsTheLaravelVueFixture(): void
    {
        $fixture = $this->laravelVueFixture();

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
    }

    public function testPhpStanLaravelConfigReportsThePlantedPhpErrorInTheLaravelVueFixture(): void
    {
        $fixture = $this->laravelVueFixture();

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

    public function testPintLaravelPresetAcceptsTheLaravelInertiaFixture(): void
    {
        $fixture = $this->laravelInertiaFixture();

        // The Vue side of the fixture lives in resources/js and is covered by
        // the ESLint and Prettier tests; Pint only reads the PHP paths.
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

    public function testPhpStanLaravelConfigAcceptsTheLaravelInertiaFixture(): void
    {
        $fixture = $this->laravelInertiaFixture();

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
    }

    public function testPhpStanLaravelConfigReportsThePlantedPhpErrorInTheLaravelInertiaFixture(): void
    {
        $fixture = $this->laravelInertiaFixture();

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

    public function testPintPerPresetAcceptsTheRawVueFixture(): void
    {
        $fixture = $this->rawVueFixture();

        // The Vue side of the fixture lives in resources/js and is covered by
        // the ESLint and Prettier tests; Pint only reads the PHP paths.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            'vendor/bin/pint',
            '--test',
            '--config',
            'vendor/waynestate/pinstripe/pint/per.json',
            'src',
        ], $fixture);

        $this->assertSame(0, $exit, $output);
    }

    public function testPhpStanRawConfigAcceptsTheRawVueFixture(): void
    {
        $fixture = $this->rawVueFixture();

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

        $this->assertSame(0, $exit, $output);
    }

    public function testPhpStanRawConfigReportsThePlantedPhpErrorInTheRawVueFixture(): void
    {
        $fixture = $this->rawVueFixture();

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
        $this->assertStringContainsString('Access to an undefined property App\Greeting::$audience', $output);
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

    public function testLaravelPresetReportsAnEnvCallOutsideConfig(): void
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
            'src/EnvLeak.php',
        ], $fixture);

        $this->assertNotSame(0, $exit, $output);
        $this->assertStringContainsString('outside of the config directory', $output);
    }

    public function testLaravelPresetAcceptsAConfigCallOutsideConfig(): void
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
            'src/ConfigRead.php',
        ], $fixture);

        $this->assertSame(0, $exit, $output);
    }

    public function testLaravelPresetReportsTlsVerificationTurnedOff(): void
    {
        // Both spellings: the facade call (a static call Larastan resolves
        // to PendingRequest) and the method partway down a chain.
        [$exit, $output, $messages] = $this->runPhpStanJson([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            'src/InsecureHttp.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertSame(
            [['pinstripe.banned.withoutVerifying', 19], ['pinstripe.banned.withoutVerifying', 24]],
            array_map(static fn (array $message): array => [$message[0], $message[2]], $messages),
            $output
        );

        foreach ($messages as [, $message]) {
            $this->assertStringContainsString('Illuminate\\Http\\Client\\PendingRequest::withoutVerifying()', $message);
            $this->assertStringContainsString('it turns off TLS certificate checks', $message);
            $this->assertStringEndsWith('(OWASP A02:2025 Security Misconfiguration).', $message);
        }
    }

    public function testLaravelPresetReportsTheVerifyOptionSetToFalse(): void
    {
        [$exit, $output, $messages] = $this->runPhpStanJson([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            'src/VerifyFalse.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertSame(
            [
                ['pinstripe.banned.verifyFalse', 'Illuminate\\Http\\Client\\PendingRequest::withOptions()', 22],
                ['pinstripe.banned.verifyFalse', 'Illuminate\\Http\\Client\\PendingRequest::withOptions()', 27],
                ['pinstripe.banned.verifyFalse', 'GuzzleHttp\\Client::__construct()', 32],
                ['pinstripe.banned.verifyFalse', 'GuzzleHttp\\ClientInterface::request()', 37],
                ['pinstripe.banned.verifyFalse', 'GuzzleHttp\\Client::get()', 42],
            ],
            array_map(
                static fn (array $message): array => [$message[0], (string) preg_replace('/^Calling (\S+).*$/s', '$1', $message[1]), $message[2]],
                $messages
            ),
            $output
        );

        foreach ($messages as [, $message]) {
            $this->assertStringContainsString('it turns off TLS certificate checks (the verify option set to false)', $message);
            $this->assertStringContainsString('(OWASP A02:2025 Security Misconfiguration).', $message);
        }
    }

    public function testLaravelPresetAcceptsVerifyPointedAtACaBundle(): void
    {
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            '--error-format=raw',
            'src/VerifiedHttp.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertSame(0, $exit, $output);
    }

    public function testLaravelPresetReportsDdAndDumpMethodsLeftIn(): void
    {
        // Laravel's own dd() and dump() methods carry the same identifiers
        // as the functions: on a collection, a query, a request, an HTTP
        // response and straight off the Http facade.
        [$exit, $output, $messages] = $this->runPhpStanJson([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            'src/LeftoverDebugMethods.php',
        ], $this->root() . '/tests/fixtures/laravel');

        $this->assertNotSame(0, $exit, $output);
        $this->assertSame(
            [
                ['pinstripe.banned.dump', 'Illuminate\\Support\\Collection::dump()', 25],
                ['pinstripe.banned.dd', 'Illuminate\\Support\\Collection::dd()', 33],
                ['pinstripe.banned.dd', 'Illuminate\\Database\\Query\\Builder::dd()', 38],
                ['pinstripe.banned.dump', 'Illuminate\\Database\\Query\\Builder::dumpRawSql()', 43],
                ['pinstripe.banned.dump', 'Illuminate\\Http\\Request::dump()', 48],
                ['pinstripe.banned.dd', 'Illuminate\\Http\\Client\\Response::dd()', 53],
                ['pinstripe.banned.dump', 'Illuminate\\Http\\Client\\PendingRequest::dump()', 58],
            ],
            array_map(
                static fn (array $message): array => [$message[0], (string) preg_replace('/^Calling (\S+).*$/s', '$1', $message[1]), $message[2]],
                $messages
            ),
            $output
        );

        foreach ($messages as [$identifier, $message]) {
            $this->assertStringContainsString($identifier === 'pinstripe.banned.dd' ? 'a dd() left in' : 'a dump() left in', $message);
            $this->assertStringContainsString('(OWASP A02:2025 Security Misconfiguration).', $message);
        }
    }

    public function testLaravelPresetAcceptsASchemaDump(): void
    {
        // SchemaState::dump() writes the schema file behind
        // php artisan schema:dump; it shares the name, not the leak.
        [$exit, $output] = $this->runInDir([
            PHP_BINARY,
            $this->root() . '/vendor/bin/phpstan',
            'analyse',
            '-c',
            'phpstan.neon',
            '--no-progress',
            '--error-format=raw',
            'src/SchemaDump.php',
        ], $this->root() . '/tests/fixtures/laravel');

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

    /**
     * A temporary Laravel + Vue app fixture, installed on first use.
     *
     * Same install story as laravelAppFixture: the fixture's composer.lock is
     * gitignored, so a fresh checkout needs an `update`, and the vendor
     * directory is shared by this class's tests and removed by
     * tearDownAfterClass.
     */
    private function laravelVueFixture(): string
    {
        self::$laravelVueDirectory ??= $this->temporaryConsumerFixture('laravel-vue');
        $fixture = self::$laravelVueDirectory;

        if (!is_file($fixture . '/vendor/autoload.php')) {
            [$installExit, $installOutput] = $this->runInDir(
                array_merge($this->composerCommand(), ['update', '--no-interaction', '--no-progress']),
                $fixture
            );

            $this->assertSame(0, $installExit, $installOutput);
        }

        return $fixture;
    }

    /**
     * A temporary Laravel + Inertia + Vue app fixture, installed on first use.
     *
     * Same install story as laravelVueFixture: the fixture's composer.lock is
     * gitignored, so a fresh checkout needs an `update`, and the vendor
     * directory is shared by this class's tests and removed by
     * tearDownAfterClass.
     */
    private function laravelInertiaFixture(): string
    {
        self::$laravelInertiaDirectory ??= $this->temporaryConsumerFixture('laravel-inertia');
        $fixture = self::$laravelInertiaDirectory;

        if (!is_file($fixture . '/vendor/autoload.php')) {
            [$installExit, $installOutput] = $this->runInDir(
                array_merge($this->composerCommand(), ['update', '--no-interaction', '--no-progress']),
                $fixture
            );

            $this->assertSame(0, $installExit, $installOutput);
        }

        return $fixture;
    }

    /**
     * A temporary raw PHP + Vue app fixture, installed on first use.
     *
     * Same install story as laravelVueFixture: the fixture's composer.lock is
     * gitignored, so a fresh checkout needs an `update`, and the vendor
     * directory is shared by this class's tests and removed by
     * tearDownAfterClass.
     */
    private function rawVueFixture(): string
    {
        self::$rawVueDirectory ??= $this->temporaryConsumerFixture('raw-vue');
        $fixture = self::$rawVueDirectory;

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

    public function testPintStrictConfigsDeclareTheirPresetsAndTheRuleStrictTypes(): void
    {
        $root = $this->root();

        $perStrict = json_decode((string) file_get_contents($root . '/pint/per-strict.json'), true, 512, JSON_THROW_ON_ERROR);
        $laravelStrict = json_decode((string) file_get_contents($root . '/pint/laravel-strict.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('per', $perStrict['preset']);
        $this->assertSame('laravel', $laravelStrict['preset']);
        $this->assertTrue($perStrict['rules']['declare_strict_types']);
        $this->assertTrue($laravelStrict['rules']['declare_strict_types']);
    }
}
