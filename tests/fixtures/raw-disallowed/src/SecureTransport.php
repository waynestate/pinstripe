<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\RawDisallowed;

use CurlHandle;

/**
 * The clean twin of InsecureTransport: verification set on explicitly, a
 * private CA bundle, and false values on options that have nothing to do
 * with TLS. Every cut has nothing to report here.
 */
final class SecureTransport
{
    public function curl(CurlHandle $handle): void
    {
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($handle, CURLOPT_CAINFO, '/etc/ssl/certs/campus-ca.pem');
        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt_array($handle, [
            CURLOPT_URL => 'https://example.edu/',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => false,
        ]);
    }

    /**
     * @return resource
     */
    public function stream()
    {
        return stream_context_create([
            'http' => ['timeout' => 5, 'ignore_errors' => false],
            'ssl' => ['verify_peer' => true, 'cafile' => '/etc/ssl/certs/campus-ca.pem'],
        ]);
    }
}
