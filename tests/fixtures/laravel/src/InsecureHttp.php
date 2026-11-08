<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\Laravel;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Deliberately banned: both ways an app turns off TLS certificate checks in
 * Laravel's HTTP client, straight off the facade and partway down a chain.
 * The Laravel preset reports each one.
 */
final class InsecureHttp
{
    public function fromTheFacade(string $url): Response
    {
        return Http::withoutVerifying()->get($url);
    }

    public function fromAChain(string $url): Response
    {
        return Http::acceptJson()->withoutVerifying()->get($url);
    }
}
