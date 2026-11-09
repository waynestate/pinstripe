<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\Laravel;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Deliberately banned: SQL joined from strings and a variable through
 * Laravel's connection, off the DB facade, a named connection, an injected
 * ConnectionInterface and an Eloquent builder. The Laravel preset reports
 * the first six as pinstripe.banned.joinedSql. The last three are the
 * builder's raw methods, which Laravel itself types as literal-string, so
 * PHPStan reports them as argument.type with no help from Pinstripe.
 */
final class JoinedQueries
{
    /**
     * @param Builder<Model> $models
     */
    public function lookUp(ConnectionInterface $db, Builder $models, string $id): void
    {
        DB::select('select * from users where id = ' . $id);
        DB::connection('oracle')->select("select * from people where person_id = '{$id}'");
        DB::statement('delete from sessions where user_id = ' . $id);
        $db->insert('insert into audit (user_id) values (' . $id . ')');
        $db->scalar('select count(*) from users where id = ' . $id);
        $models->fromQuery('select * from users where id = ' . $id);

        DB::table('users')->whereRaw('id = ' . $id)->get();
        DB::table('users')->orderByRaw($id)->get();
        DB::unprepared('drop table ' . $id);
    }
}
