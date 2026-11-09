<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\Laravel;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Deliberately banned: Laravel's own dd() and dump() methods, left in after
 * a fitting. Each sits in its own method, because a dd() never returns and
 * would hide everything after it. The Laravel preset reports every one, by
 * the same identifiers as the dd() and dump() functions.
 */
final class LeftoverDebugMethods
{
    /**
     * @param Collection<int, string> $names
     */
    public function collectionDump(Collection $names): int
    {
        return $names->dump()->count();
    }

    /**
     * @param Collection<int, string> $names
     */
    public function collectionDd(Collection $names): void
    {
        $names->dd();
    }

    public function queryDd(): void
    {
        DB::table('users')->where('id', 1)->dd();
    }

    public function queryDumpRawSql(): int
    {
        return DB::table('users')->dumpRawSql()->count();
    }

    public function requestDump(Request $request): string
    {
        return $request->dump()->path();
    }

    public function responseDd(string $url): void
    {
        Http::get($url)->dd();
    }

    public function facadeDump(string $url): int
    {
        return Http::dump()->get($url)->status();
    }
}
