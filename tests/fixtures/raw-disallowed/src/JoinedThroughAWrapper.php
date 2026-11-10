<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawDisallowed;

/**
 * Deliberately broken: SQL joined from strings handed to a wrapper. Through
 * SqlFiles, whose parameters are literal-string, PHPStan reports each
 * joined caller (argument.type). Through LooseWrapper, whose parameter is a
 * plain string, the ban fires once, inside the wrapper, and never at the
 * caller: baseline that one line and every caller goes unchecked for good.
 */
final class JoinedThroughAWrapper
{
    /**
     * @param resource $oracle
     */
    public static function person($oracle, string $id): void
    {
        SqlFiles::parse($oracle, 'SELECT * FROM people WHERE person_id = ' . $id);
        SqlFiles::query('people_' . $id);
        LooseWrapper::parse($oracle, 'SELECT * FROM people WHERE person_id = ' . $id);
    }
}

/**
 * The wrapper typed the old way. Its one report is the only sign that its
 * callers join SQL: a loose thread that unravels the whole sleeve.
 */
final class LooseWrapper
{
    /**
     * @param resource $oracle
     *
     * @return resource|false
     */
    public static function parse($oracle, string $sql)
    {
        return oci_parse($oracle, $sql);
    }
}
