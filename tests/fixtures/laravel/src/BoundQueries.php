<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\Laravel;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

/**
 * The clean twin of JoinedQueries: literal SQL with bindings, and the query
 * builder's own insert(), update(), delete() and select(), which take
 * arrays and column names rather than SQL. The Laravel preset has nothing
 * to report here.
 */
final class BoundQueries
{
    private const TABLE = 'users';

    /**
     * @param list<string> $columns
     */
    public function lookUp(ConnectionInterface $db, string $id, array $columns): void
    {
        DB::select('select * from users where id = ?', [$id]);
        DB::connection('oracle')->select('select * from people where person_id = :id', ['id' => $id]);
        DB::statement('delete from sessions where user_id = ?', [$id]);
        $db->insert('insert into audit (user_id) values (?)', [$id]);
        $db->scalar('select count(*) from ' . self::TABLE);

        DB::table('users')->select($columns)->where('id', $id)->get();
        DB::table('audit')->insert(['user_id' => $id]);
        DB::table('users')->where('id', $id)->update(['active' => false]);
        DB::table('sessions')->where('user_id', $id)->delete();
        DB::table('users')->whereRaw('id = ?', [$id])->get();
    }
}
