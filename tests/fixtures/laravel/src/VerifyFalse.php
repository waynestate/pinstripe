<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\Laravel;

use GuzzleHttp\Client;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;

/**
 * Deliberately banned: the verify option set to false, through Laravel's
 * withOptions() (off the facade and partway down a chain) and through
 * Guzzle (the client's config, request() and a verb method). The Laravel
 * preset reports each one.
 */
final class VerifyFalse
{
    public function fromTheFacade(string $url): Response
    {
        return Http::withOptions(['verify' => false])->get($url);
    }

    public function fromAChain(string $url): Response
    {
        return Http::acceptJson()->withOptions(['timeout' => 5, 'verify' => false])->get($url);
    }

    public function guzzleClient(): Client
    {
        return new Client(['base_uri' => 'https://example.edu/', 'verify' => false]);
    }

    public function guzzleRequest(Client $client, string $url): ResponseInterface
    {
        return $client->request('GET', $url, ['verify' => false]);
    }

    public function guzzleGet(Client $client, string $url): ResponseInterface
    {
        return $client->get($url, ['verify' => false]);
    }
}
