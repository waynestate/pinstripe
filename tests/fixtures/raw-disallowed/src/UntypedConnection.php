<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawDisallowed;

/**
 * Deliberately broken, twice over, and only one of them shows. The same
 * joined query runs on a connection pulled in with `global`, the way an old
 * include hands it out. Left untyped, the connection is mixed, PHPStan cannot
 * tell it is a PDO, and the ban stays quiet (line 22). One `@var` names the
 * type, and the same line is reported (line 32). A tailor cannot take in a
 * seam he cannot see.
 */
final class UntypedConnection
{
    public static function untyped(string $id): void
    {
        global $db;

        // Not reported: PHPStan does not know what $db is.
        $db->query('SELECT * FROM people WHERE person_id = ' . $id);
    }

    public static function typed(string $id): void
    {
        global $db;

        // Reported as pinstripe.banned.joinedSql: the same query, on a
        // connection PHPStan can now see.
        /** @var \PDO $db */
        $db->query('SELECT * FROM people WHERE person_id = ' . $id);
    }
}
