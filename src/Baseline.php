<?php

declare(strict_types=1);

namespace WayneState\Pinstripe;

use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;

/**
 * Counts the ignored errors in a PHPStan baseline.
 *
 * Both formats PHPStan writes are supported: a NEON file with a
 * `parameters.ignoreErrors` list, and a PHP file returning
 * `['parameters' => ['ignoreErrors' => [...]]]`. Each entry counts as its
 * `count`, or as 1 when `count` is absent, so the total is how many errors
 * the baseline currently lets through. A baseline may only shrink, and this
 * class is the tape measure that keeps the chalk line honest.
 */
final class Baseline
{
    /**
     * Count the ignored errors in the baseline file at $path.
     */
    public static function countFile(string $path): int
    {
        if (!is_file($path)) {
            throw BaselineNotFoundException::forPath($path);
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new BaselineException('could not read baseline file: ' . $path);
        }

        return self::countContents($contents, $path);
    }

    /**
     * Count the ignored errors in raw baseline contents, as read from disk
     * or from `git show <ref>:<path>`. $label names the source in errors.
     */
    public static function countContents(string $contents, string $label): int
    {
        $decoded = self::decode($contents, $label);

        $errors = $decoded['parameters']['ignoreErrors'] ?? [];
        if (!is_array($errors)) {
            throw new BaselineException($label . ': parameters.ignoreErrors must be a list of errors.');
        }

        $total = 0;
        foreach ($errors as $entry) {
            if (is_array($entry) && isset($entry['count']) && is_int($entry['count'])) {
                $total += $entry['count'];
            } else {
                // A plain message, or an entry without a count: one error.
                $total += 1;
            }
        }

        return $total;
    }

    /**
     * Count the ignored errors in $path as it was committed at git ref $ref.
     *
     * $path is relative to the repository root, exactly as `git show` reads
     * it, so run this from anywhere inside the repository.
     */
    public static function countAtRef(string $path, string $ref): int
    {
        return self::countContents(self::gitShow($ref, $path), $ref . ':' . $path);
    }

    /**
     * @return array<string, mixed>
     */
    private static function decode(string $contents, string $label): array
    {
        if (str_starts_with(ltrim($contents), '<?php')) {
            return self::requireContents($contents, $label);
        }

        try {
            $decoded = Neon::decode($contents);
        } catch (NeonException $exception) {
            throw new BaselineException($label . ': invalid NEON: ' . $exception->getMessage());
        }

        if (!is_array($decoded)) {
            throw new BaselineException(
                $label . ': expected an array of parameters, got ' . get_debug_type($decoded) . '.'
            );
        }

        return $decoded;
    }

    /**
     * Require the PHP-format baseline from a temporary file.
     *
     * @return array<string, mixed>
     */
    private static function requireContents(string $contents, string $label): array
    {
        $temporary = tempnam(sys_get_temp_dir(), 'pinstripe-baseline-');
        if ($temporary === false) {
            throw new BaselineException($label . ': could not create a temporary file.');
        }

        if (file_put_contents($temporary, $contents) === false) {
            unlink($temporary);

            throw new BaselineException($label . ': could not write the temporary file.');
        }

        try {
            try {
                $decoded = require $temporary;
            } catch (\ParseError $exception) {
                throw new BaselineException($label . ': invalid PHP: ' . $exception->getMessage());
            }
        } finally {
            unlink($temporary);
        }

        if (!is_array($decoded)) {
            throw new BaselineException($label . ': the PHP baseline must return an array of parameters.');
        }

        return $decoded;
    }

    /**
     * Run `git show <ref>:<path>` without a shell, on every OS.
     */
    private static function gitShow(string $ref, string $path): string
    {
        $process = proc_open(
            ['git', 'show', $ref . ':' . $path],
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (!is_resource($process)) {
            throw new BaselineException("could not run git show for {$ref}:{$path}.");
        }

        $stdout = stream_get_contents($pipes[1]) ?: '';
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exit = proc_close($process);

        if ($exit !== 0) {
            $reason = trim($stderr);

            throw new BaselineException(
                "could not read {$path} at {$ref}" . ($reason !== '' ? ': ' . $reason : '.')
            );
        }

        return $stdout;
    }
}
