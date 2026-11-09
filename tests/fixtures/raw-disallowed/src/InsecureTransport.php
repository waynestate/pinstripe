<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawDisallowed;

use CurlHandle;

/**
 * Deliberately banned: every way a raw PHP app turns TLS checks off, through
 * curl and through a stream context. Every cut reports each one, and nothing
 * else, which PresetsTest checks by identifier and line.
 */
final class InsecureTransport
{
    public function peerOff(CurlHandle $handle): void
    {
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
    }

    public function hostOff(CurlHandle $handle): void
    {
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
    }

    public function peerOffByName(CurlHandle $handle): void
    {
        curl_setopt(handle: $handle, option: CURLOPT_SSL_VERIFYPEER, value: false);
    }

    public function peerOffInAnArray(CurlHandle $handle): void
    {
        curl_setopt_array($handle, [CURLOPT_URL => 'https://example.edu/', CURLOPT_SSL_VERIFYPEER => false]);
    }

    public function hostOffInAnArray(CurlHandle $handle): void
    {
        curl_setopt_array($handle, [CURLOPT_SSL_VERIFYHOST => 0]);
    }

    /**
     * @return resource
     */
    public function streamPeerOff()
    {
        return stream_context_create(['ssl' => ['verify_peer' => false]]);
    }

    /**
     * @return resource
     */
    public function streamNameOff()
    {
        return stream_context_create(['http' => ['timeout' => 5], 'ssl' => ['verify_peer_name' => false]]);
    }

    /**
     * @return resource
     */
    public function streamSelfSigned()
    {
        return stream_context_create(['ssl' => ['allow_self_signed' => true]]);
    }
}
