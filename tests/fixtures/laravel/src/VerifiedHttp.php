<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\Laravel;

use GuzzleHttp\Client;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;

/**
 * The clean twin of VerifyFalse: verify pointed at a private CA bundle, or
 * left alone, with other options beside it. The Laravel preset has nothing
 * to report here.
 */
final class VerifiedHttp
{
    public function privateCa(string $url): Response
    {
        return Http::withOptions(['verify' => '/etc/ssl/certs/campus-ca.pem'])->get($url);
    }

    public function otherOptions(string $url): Response
    {
        return Http::acceptJson()->withOptions(['timeout' => 5])->get($url);
    }

    public function guzzle(string $url): ResponseInterface
    {
        $client = new Client(['base_uri' => 'https://example.edu/', 'verify' => '/etc/ssl/certs/campus-ca.pem']);
        $client->request('GET', $url, ['timeout' => 5]);

        return $client->get($url);
    }
}
