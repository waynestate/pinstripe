<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\Laravel;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\SchemaState;

/**
 * The clean twin of LeftoverDebugMethods: a schema dump (the work behind
 * php artisan schema:dump) is a dump() method that writes a file, not a
 * debugging leftover, so the Laravel preset has nothing to report here.
 */
final class SchemaDump
{
    public function write(SchemaState $state, Connection $connection, string $path): void
    {
        $state->dump($connection, $path);
    }
}
