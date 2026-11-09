<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawDisallowed;

use mysqli;
use PDO;

/**
 * Deliberately banned: SQL joined from strings and a variable, through PDO,
 * mysqli and oci8, by concatenation, interpolation and sprintf(). Every cut
 * reports each call as pinstripe.banned.joinedSql.
 */
final class JoinedSql
{
    /**
     * @param resource $oracle
     */
    public function lookUp(PDO $pdo, mysqli $mysql, $oracle, string $id, int $term): void
    {
        $pdo->query('SELECT * FROM users WHERE id = ' . $id);
        $pdo->prepare("SELECT * FROM users WHERE id = {$id}");
        $pdo->exec(sprintf('DELETE FROM sessions WHERE user_id = %s', $id));
        $pdo->query('SELECT * FROM classes WHERE term = ' . $term);
        $mysql->query('SELECT * FROM users WHERE id = ' . $id);
        $mysql->prepare('SELECT * FROM users WHERE id = ' . $id);
        mysqli_query($mysql, 'SELECT * FROM users WHERE id = ' . $id);
        oci_parse($oracle, "SELECT * FROM people WHERE person_id = '" . $id . "'");
    }
}
