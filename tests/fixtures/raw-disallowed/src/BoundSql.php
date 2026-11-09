<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawDisallowed;

use mysqli;
use PDO;

/**
 * The clean twin of JoinedSql: the SQL stays a literal and every value is
 * bound. Literal pieces joined together, a class constant, a literal .= on
 * a literal, and placeholders built from literals all pass. Every cut has
 * nothing to report here.
 */
final class BoundSql
{
    private const TABLE = 'users';

    /**
     * @param resource $oracle
     * @param list<int> $ids
     */
    public function lookUp(PDO $pdo, mysqli $mysql, $oracle, string $id, array $ids, bool $activeOnly): void
    {
        $pdo->prepare('SELECT * FROM users WHERE id = ?')->execute([$id]);
        $pdo->query('SELECT COUNT(*) FROM ' . self::TABLE);

        $sql = 'SELECT * FROM users WHERE 1 = 1';
        if ($activeOnly) {
            $sql .= ' AND active = 1';
        }
        $pdo->prepare($sql . ' ORDER BY id')->execute();

        $marks = implode(', ', array_fill(0, count($ids), '?'));
        $pdo->prepare('SELECT * FROM users WHERE id IN (' . $marks . ')')->execute($ids);

        $statement = $mysql->prepare('SELECT * FROM users WHERE id = ?');
        if ($statement !== false) {
            $statement->bind_param('s', $id);
        }

        $parsed = oci_parse($oracle, 'SELECT * FROM people WHERE person_id = :id');
        if ($parsed !== false) {
            oci_bind_by_name($parsed, ':id', $id);
        }
    }
}
