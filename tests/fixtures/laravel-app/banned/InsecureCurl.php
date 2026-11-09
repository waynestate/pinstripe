<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\LaravelApp\Banned;

use CurlHandle;

/**
 * Deliberately banned: curl told to skip the certificate check, in an app
 * that pulls Pinstripe in through Composer. The curl_setopt() ban is
 * Pinstripe's own PHPStan rule, so this proves the rule's class autoloads
 * from the installed package, not only from this repo.
 */
final class InsecureCurl
{
    public function skipTheFitting(CurlHandle $handle): void
    {
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
    }
}
