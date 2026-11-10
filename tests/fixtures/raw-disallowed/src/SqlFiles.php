<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawDisallowed;

use RuntimeException;

/**
 * The house pattern behind most joined-SQL reports in real apps: the SQL
 * lives in the app's own .sql files and every value is bound. Typed this
 * way it passes every cut with no baseline. The reader says its SQL is a
 * literal (one reasoned ignore, in one place), and the wrapper takes only
 * literal SQL, so PHPStan checks every caller. Its broken twin is
 * JoinedThroughAWrapper. Made to measure, so nothing needs letting out.
 */
final class SqlFiles
{
    /**
     * Read one query from the app's own sql/ folder. The name is a literal
     * too, so a request value can never pick the file.
     *
     * @param literal-string $name
     *
     * @return literal-string
     */
    public static function query(string $name): string
    {
        $sql = file_get_contents(__DIR__ . '/sql/' . $name . '.sql');
        if ($sql === false) {
            throw new RuntimeException('No such query: ' . $name);
        }

        // @phpstan-ignore return.type (the SQL is read from the app's own sql/ folder, and every value is bound)
        return $sql;
    }

    /**
     * Parse SQL for Oracle. The parameter is literal-string, not string, so
     * PHPStan checks each caller instead of reporting this one line.
     *
     * @param resource $oracle
     * @param literal-string $sql
     *
     * @return resource|false
     */
    public static function parse($oracle, string $sql)
    {
        return oci_parse($oracle, $sql);
    }

    /**
     * @param resource $oracle
     */
    public static function person($oracle, string $id): void
    {
        $statement = self::parse($oracle, self::query('people'));
        if ($statement !== false) {
            oci_bind_by_name($statement, ':id', $id);
        }

        self::parse($oracle, 'SELECT * FROM terms WHERE active = 1');
    }
}
