<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests\Update;

use PHPUnit\Framework\TestCase;
use WayneState\Pinstripe\Update\InstallKind;
use WayneState\Pinstripe\Update\LatestReleaseFetcher;
use WayneState\Pinstripe\Update\PackagistFetcher;
use WayneState\Pinstripe\Update\UpdateCache;
use WayneState\Pinstripe\Update\UpdateNotice;

/**
 * The update notice: one stderr line when a newer stable release exists,
 * with the command that updates this install. Every test uses an injected
 * fetcher and clock and a temp cache dir, so nothing here touches the
 * network or the real cache. The tape measure measures, and only measures.
 */
final class UpdateNoticeTest extends TestCase
{
    private const NOW = 1_763_000_000;

    /** @var list<string> */
    private array $tempDirs = [];

    protected function tearDown(): void
    {
        foreach ($this->tempDirs as $dir) {
            $this->removeDir($dir);
        }
        $this->tempDirs = [];
    }

    public function testLineForPhar(): void
    {
        // Pinstripe ships no PHAR, so a PHAR copy points at the Composer
        // global command, the only supported install path.
        $env = $this->env();

        [$notice, $fetcher] = $this->notice(InstallKind::Phar, [['version' => '0.5.0']], self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame(
            "pinstripe 0.4.0 is out of date: 0.5.0 is the newest release. Update: composer global update waynestate/pinstripe\n",
            $sink['text'],
        );
        self::assertSame(1, $fetcher->calls);
    }

    public function testLineForSourceCheckout(): void
    {
        // A checkout with a .git of its own (folder or file) outside any
        // vendor/ folder is a source checkout.
        $root = $this->makeTempDir() . '/checkout';
        mkdir($root . '/bin', 0777, true);
        mkdir($root . '/src', 0777, true);
        file_put_contents($root . '/.git', "# worktree gitdir marker\n");
        $env = $this->env();

        self::assertSame(InstallKind::SourceCheckout, InstallKind::detect($root, $env));

        [$notice] = $this->notice(InstallKind::SourceCheckout, [['version' => '0.5.0']], self::NOW, $root);
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame("pinstripe 0.4.0 is out of date: 0.5.0 is the newest release. Update: git pull\n", $sink['text']);
    }

    public function testLineForComposerGlobal(): void
    {
        // <global home>/vendor/waynestate/pinstripe, with the global home
        // named by COMPOSER_HOME.
        $home = $this->makeTempDir() . '/composer-home';
        $root = $home . '/vendor/waynestate/pinstripe';
        mkdir($root . '/bin', 0777, true);
        $env = $this->env(['COMPOSER_HOME' => $home]);

        self::assertSame(InstallKind::ComposerGlobal, InstallKind::detect($root, $env));

        [$notice] = $this->notice(InstallKind::ComposerGlobal, [['version' => '0.5.0']], self::NOW, $root);
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame(
            "pinstripe 0.4.0 is out of date: 0.5.0 is the newest release. Update: composer global update waynestate/pinstripe\n",
            $sink['text'],
        );
    }

    public function testLineForProjectDependency(): void
    {
        // <project>/vendor/waynestate/pinstripe, a project that is not
        // Composer's global home, pinning a constraint that still allows the
        // newest release.
        $project = $this->makeTempDir() . '/app';
        $root = $project . '/vendor/waynestate/pinstripe';
        mkdir($root . '/bin', 0777, true);
        file_put_contents($project . '/composer.json', (string) json_encode([
            'require' => ['waynestate/pinstripe' => '^0.4'],
        ], JSON_PRETTY_PRINT));
        $env = $this->env();

        self::assertSame(InstallKind::ProjectDependency, InstallKind::detect($root, $env));

        [$notice] = $this->notice(InstallKind::ProjectDependency, [['version' => '0.4.3']], self::NOW, $root);
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame(
            "pinstripe 0.4.0 is out of date: 0.4.3 is the newest release. Update: composer update waynestate/pinstripe\n",
            $sink['text'],
        );
    }

    public function testLineForPinnedProjectDependency(): void
    {
        // A ^ or ~ pin that excludes the newest release switches the line to
        // a require of the newest major.minor; --dev only when the pin sits
        // in require-dev.
        $project = $this->makeTempDir() . '/app';
        $root = $project . '/vendor/waynestate/pinstripe';
        mkdir($root . '/bin', 0777, true);
        file_put_contents($project . '/composer.json', (string) json_encode([
            'require' => ['waynestate/pinstripe' => '^0.4'],
        ], JSON_PRETTY_PRINT));
        $env = $this->env();

        [$notice] = $this->notice(InstallKind::ProjectDependency, [['version' => '0.5.0']], self::NOW, $root);
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame(
            "pinstripe 0.4.0 is out of date: 0.5.0 is the newest release. Update: composer require waynestate/pinstripe:^0.5\n",
            $sink['text'],
        );

        // The same pin in require-dev adds --dev.
        file_put_contents($project . '/composer.json', (string) json_encode([
            'require-dev' => ['waynestate/pinstripe' => '~0.4'],
        ], JSON_PRETTY_PRINT));
        $env = $this->env();

        [$notice] = $this->notice(InstallKind::ProjectDependency, [['version' => '0.5.0']], self::NOW, $root);
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame(
            "pinstripe 0.4.0 is out of date: 0.5.0 is the newest release. Update: composer require waynestate/pinstripe:^0.5 --dev\n",
            $sink['text'],
        );
    }

    public function testLineForUnknownInstall(): void
    {
        // No .git, not under any vendor/: unknown, which falls back to the
        // Composer global command.
        $root = $this->makeTempDir() . '/loose';
        mkdir($root . '/bin', 0777, true);
        $env = $this->env();

        self::assertSame(InstallKind::Unknown, InstallKind::detect($root, $env));

        [$notice] = $this->notice(InstallKind::Unknown, [['version' => '0.5.0']], self::NOW, $root);
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame(
            "pinstripe 0.4.0 is out of date: 0.5.0 is the newest release. Update: composer global update waynestate/pinstripe\n",
            $sink['text'],
        );
    }

    public function testCachedResultMakesNoFetch(): void
    {
        // A cache checked within the last day is printed from, without a
        // fetch, even when the fetcher would answer differently.
        $env = $this->env();
        $this->seedCache($env, self::NOW, '0.5.0');

        [$notice, $fetcher] = $this->notice(InstallKind::SourceCheckout, [['version' => '9.9.9']], self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame("pinstripe 0.4.0 is out of date: 0.5.0 is the newest release. Update: git pull\n", $sink['text']);
        self::assertSame(0, $fetcher->calls);
    }

    public function testStaleCacheFetchesOnce(): void
    {
        // A cache older than a day fetches once and rewrites the file.
        $env = $this->env();
        $path = $this->seedCache($env, self::NOW - 86401, '0.4.0');

        [$notice, $fetcher] = $this->notice(InstallKind::SourceCheckout, [['version' => '0.5.0']], self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame("pinstripe 0.4.0 is out of date: 0.5.0 is the newest release. Update: git pull\n", $sink['text']);
        self::assertSame(1, $fetcher->calls);
        self::assertSame(['checked_at' => self::NOW, 'latest' => '0.5.0'], (new UpdateCache('pinstripe'))->read($path));
    }

    public function testOfflineIsSilent(): void
    {
        // No cache and a failed fetch: nothing is printed, and the empty
        // result is stamped so the next command does not try again for a
        // day.
        $env = $this->env();
        $path = (new UpdateCache('pinstripe'))->path($env);

        [$notice, $fetcher] = $this->notice(InstallKind::SourceCheckout, null, self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame('', $sink['text']);
        self::assertSame(1, $fetcher->calls);
        self::assertSame(['checked_at' => self::NOW, 'latest' => null], (new UpdateCache('pinstripe'))->read($path));
    }

    public function testMalformedJsonIsSilent(): void
    {
        // Malformed p2 bodies parse to null, and a null fetch stays silent
        // with the stamp refreshed.
        self::assertNull(PackagistFetcher::parse('not json'));
        self::assertNull(PackagistFetcher::parse('{"packages": {}}'));
        self::assertNull(PackagistFetcher::parse('{"packages": {"waynestate/pinstripe": "nope"}}'));
        self::assertSame(
            [['version' => '0.5.0']],
            PackagistFetcher::parse('{"packages": {"waynestate/pinstripe": [{"version": "0.5.0"}]}}'),
        );

        $env = $this->env();
        $path = (new UpdateCache('pinstripe'))->path($env);

        [$notice, $fetcher] = $this->notice(InstallKind::SourceCheckout, null, self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame('', $sink['text']);
        self::assertSame(1, $fetcher->calls);
        self::assertSame(['checked_at' => self::NOW, 'latest' => null], (new UpdateCache('pinstripe'))->read($path));
    }

    public function testCiEnvIsSilent(): void
    {
        // CI set to anything other than empty, 0 or false stays silent
        // before the cache is even read.
        $env = $this->env(['CI' => '1']);
        $this->seedCache($env, self::NOW, '0.5.0');

        [$notice, $fetcher] = $this->notice(InstallKind::SourceCheckout, [['version' => '9.9.9']], self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame('', $sink['text']);
        self::assertSame(0, $fetcher->calls);
    }

    public function testOptOutEnvIsSilent(): void
    {
        $env = $this->env(['WAYNE_NO_UPDATE_CHECK' => '1']);
        $this->seedCache($env, self::NOW, '0.5.0');

        [$notice, $fetcher] = $this->notice(InstallKind::SourceCheckout, [['version' => '9.9.9']], self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame('', $sink['text']);
        self::assertSame(0, $fetcher->calls);
    }

    public function testQuietIsSilent(): void
    {
        $env = $this->env();
        $this->seedCache($env, self::NOW, '0.5.0');

        [$notice, $fetcher] = $this->notice(InstallKind::SourceCheckout, [['version' => '9.9.9']], self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count', '--quiet'], '0.4.0', $write);

        self::assertSame('', $sink['text']);
        self::assertSame(0, $fetcher->calls);

        // The short form is quiet too.
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count', '-q'], '0.4.0', $write);

        self::assertSame('', $sink['text']);
    }

    public function testStdoutUntouchedByJsonFormat(): void
    {
        // The contract's requirement is that stdout is byte-identical with
        // the check on and off. Run the real binary twice, copied into a
        // project-dependency layout the way BaselineTest does: an app whose
        // vendor holds a copy of the package, with a shim autoloader that
        // registers the running version the way Composer would. A seeded
        // cache makes the notice print without touching the network. CI and
        // the opt-out are pinned to empty so an ambient environment cannot
        // silence the check.
        $home = $this->makeTempDir();
        $bin = $home . '/vendor/waynestate/pinstripe/bin';
        mkdir($bin, 0777, true);
        $this->assertTrue(copy(dirname(__DIR__) . '/../bin/pinstripe-baseline', $bin . '/pinstripe-baseline'));

        // The app opts in with the legacy flag and carries the six-error
        // baseline fixture.
        file_put_contents($home . '/composer.json', (string) json_encode([
            'name' => 'wayne-state/update-test-app',
            'extra' => ['pinstripe' => ['legacy' => true]],
        ], JSON_PRETTY_PRINT));
        $this->assertTrue(copy(dirname(__DIR__) . '/../tests/fixtures/baseline/phpstan-baseline.neon', $home . '/phpstan-baseline.neon'));

        // The app's autoloader loads the package's real autoloader, then
        // registers a second ClassLoader whose vendor directory holds its
        // own composer/installed.php naming the running version, the way an
        // installed package would. InstalledVersions scans registered
        // loaders before the repository's own installed.php, so the 0.4.0
        // below wins over the root package's 1.0.0+no-version-set.
        file_put_contents($home . '/vendor/autoload.php', "<?php\n\nrequire "
            . var_export(dirname(dirname(__DIR__)) . '/vendor/autoload.php', true) . ";\n\n"
            . "\$shimLoader = new \\Composer\\Autoload\\ClassLoader(__DIR__);\n"
            . "\$shimLoader->register(true);\n");

        mkdir($home . '/vendor/composer', 0777, true);
        file_put_contents($home . '/vendor/composer/installed.php', "<?php\n\nreturn [\n"
            . "    'root' => [\n"
            . "        'name' => 'wayne-state/update-test-app',\n"
            . "        'pretty_version' => '1.0.0+no-version-set',\n"
            . "        'version' => '1.0.0.0',\n"
            . "        'reference' => null,\n"
            . "        'type' => 'project',\n"
            . "        'install_path' => __DIR__ . '/../../',\n"
            . "        'aliases' => [],\n"
            . "        'dev' => false,\n"
            . "    ],\n"
            . "    'versions' => [\n"
            . "        'waynestate/pinstripe' => [\n"
            . "            'pretty_version' => '0.4.0',\n"
            . "            'version' => '0.4.0.0',\n"
            . "            'reference' => null,\n"
            . "            'type' => 'library',\n"
            . "            'install_path' => __DIR__ . '/../waynestate/pinstripe',\n"
            . "            'aliases' => [],\n"
            . "            'dev_requirement' => false,\n"
            . "        ],\n"
            . "    ],\n"
            . "];\n");

        // 0.9.9 stays on the same major as the running 0.4.0, so the line
        // carries no upgrading link.
        $cacheDir = $home . '/cache';
        $file = $cacheDir . '/waynestate/pinstripe/update-check.json';
        mkdir(dirname($file), 0777, true);
        file_put_contents($file, json_encode(['checked_at' => time(), 'latest' => '0.9.9']) . "\n");

        $env = ['CI' => '', 'WAYNE_NO_UPDATE_CHECK' => '', 'XDG_CACHE_HOME' => $cacheDir];
        [$stdoutOff, $stderrOff, $exitOff] = $this->runBinary($home, [...$env, 'WAYNE_NO_UPDATE_CHECK' => '1']);
        [$stdoutOn, $stderrOn, $exitOn] = $this->runBinary($home, $env);

        self::assertSame(0, $exitOff, "stderr: {$stderrOff}");
        self::assertSame($exitOff, $exitOn, "stderr: {$stderrOn}");
        self::assertSame($stdoutOff, $stdoutOn, "stderr: {$stderrOn}");
        self::assertSame("6\n", $stdoutOn);

        // The notice went to stderr, on one line. The copied layout is a
        // project dependency without a pinstripe pin, so the line ends with
        // the plain update command.
        $lines = array_values(array_filter(explode("\n", $stderrOn), static fn (string $line): bool => $line !== ''));
        self::assertCount(1, $lines, "stderr: {$stderrOn}");
        self::assertSame(
            'pinstripe 0.4.0 is out of date: 0.9.9 is the newest release. Update: composer update waynestate/pinstripe',
            $lines[0],
        );
    }

    public function testNormalisesVPrefix(): void
    {
        // Tags may start with v or not: both normalise, and the line prints
        // the version without the v.
        $env = $this->env();
        $entries = [
            ['version' => 'v0.5.0'],
            ['version' => '0.4.9'],
            ['version' => 'v0.4.0'],
        ];

        [$notice] = $this->notice(InstallKind::SourceCheckout, $entries, self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame("pinstripe 0.4.0 is out of date: 0.5.0 is the newest release. Update: git pull\n", $sink['text']);
    }

    public function testIgnoresPrereleases(): void
    {
        // dev, alpha, beta and rc versions (any case) are dropped, so the
        // line names the highest stable release.
        $env = $this->env();
        $entries = [
            ['version' => '1.0.0-beta1'],
            ['version' => '1.0.0-RC1'],
            ['version' => 'dev-main'],
            ['version' => '0.5.0-alpha'],
            ['version' => '0.4.6-BETA'],
            ['version' => '0.4.5'],
        ];

        [$notice] = $this->notice(InstallKind::SourceCheckout, $entries, self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame("pinstripe 0.4.0 is out of date: 0.4.5 is the newest release. Update: git pull\n", $sink['text']);
    }

    public function testMajorBumpAddsUpgradingLink(): void
    {
        $env = $this->env();

        [$notice] = $this->notice(InstallKind::SourceCheckout, [['version' => '1.0.0']], self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame(
            "pinstripe 0.4.0 is out of date: 1.0.0 is the newest release. Update: git pull Upgrading: https://pinstripe.apps.wayne.edu/docs/upgrading/\n",
            $sink['text'],
        );

        // A minor bump stays on the same major and adds no link.
        $env = $this->env();

        [$notice] = $this->notice(InstallKind::SourceCheckout, [['version' => '0.9.0']], self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame("pinstripe 0.4.0 is out of date: 0.9.0 is the newest release. Update: git pull\n", $sink['text']);
    }

    public function testNotNewerIsSilent(): void
    {
        // A running version equal to, or newer than, the cached latest,
        // prints nothing.
        $env = $this->env();
        $this->seedCache($env, self::NOW, '0.4.0');

        [$notice] = $this->notice(InstallKind::SourceCheckout, null, self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame('', $sink['text']);

        $env = $this->env();
        $this->seedCache($env, self::NOW, '0.3.9');

        [$notice] = $this->notice(InstallKind::SourceCheckout, null, self::NOW, '');
        [$write, $sink] = $this->stderr();

        $notice->run($env, ['pinstripe-baseline', 'count'], '0.4.0', $write);

        self::assertSame('', $sink['text']);
    }

    /**
     * @param array<string, string> $extra
     * @return array<string, string>
     */
    private function env(array $extra = []): array
    {
        return array_merge(['XDG_CACHE_HOME' => $this->cacheDir()], $extra);
    }

    private function cacheDir(): string
    {
        return $this->makeTempDir() . '/cache';
    }

    /**
     * @param array<string, string> $env
     */
    private function seedCache(array $env, int $checkedAt, ?string $latest): string
    {
        $cache = new UpdateCache('pinstripe');
        $path = $cache->path($env);
        self::assertTrue($cache->write($path, $checkedAt, $latest));

        return $path;
    }

    /**
     * A write closure and the sink it appends to. The sink is an
     * ArrayObject, not a plain array: the closure and the caller must see
     * the same variable, and only an object is shared by handle when the
     * helper returns.
     *
     * @return array{0: callable(string): void, 1: \ArrayObject<string, string>}
     */
    private function stderr(): array
    {
        $sink = new \ArrayObject(['text' => '']);
        $write = static function (string $message) use ($sink): void {
            $sink['text'] .= $message;
        };

        return [$write, $sink];
    }

    /**
     * @param list<array<string, mixed>>|null $entries
     * @return array{0: UpdateNotice, 1: FixedFetcher}
     */
    private function notice(InstallKind $kind, ?array $entries, int $now, string $packageRoot): array
    {
        $fetcher = new FixedFetcher($entries);
        $notice = new UpdateNotice(
            'pinstripe',
            $packageRoot,
            $kind,
            new UpdateCache('pinstripe'),
            $fetcher,
            static fn (): int => $now,
        );

        return [$notice, $fetcher];
    }

    /**
     * Run the copied bin/pinstripe-baseline count in the given directory and
     * capture stdout and stderr separately, the way BaselineTest drives the
     * script.
     *
     * @param array<string, string> $extraEnv
     * @return array{0: string, 1: string, 2: int}
     */
    private function runBinary(string $workingDir, array $extraEnv): array
    {
        $env = getenv();
        if ($env === false) {
            $env = [];
        }
        foreach ($extraEnv as $key => $value) {
            $env[$key] = $value;
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $pipes = [];
        $process = proc_open(
            [PHP_BINARY, $workingDir . '/vendor/waynestate/pinstripe/bin/pinstripe-baseline', 'count', 'phpstan-baseline.neon'],
            $descriptors,
            $pipes,
            $workingDir,
            $env,
        );
        self::assertIsResource($process);
        fclose($pipes[0]);
        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exit = proc_close($process);

        return [$stdout, $stderr, $exit === false ? -1 : $exit];
    }

    private function makeTempDir(): string
    {
        $dir = sys_get_temp_dir() . '/pinstripe-update-test-' . bin2hex(random_bytes(8));
        mkdir($dir, 0777, true);
        $this->tempDirs[] = $dir;

        return $dir;
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) ?: [] as $entry) {
            if ('.' === $entry || '..' === $entry) {
                continue;
            }
            $path = $dir . '/' . $entry;
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}

/**
 * A LatestReleaseFetcher that counts its calls and answers with fixed
 * entries, so the tests need no network.
 */
final class FixedFetcher implements LatestReleaseFetcher
{
    public int $calls = 0;

    /**
     * @param list<array<string, mixed>>|null $entries
     */
    public function __construct(private readonly ?array $entries) {}

    public function fetch(): ?array
    {
        $this->calls++;

        return $this->entries;
    }
}
