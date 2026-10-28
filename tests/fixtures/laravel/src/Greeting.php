<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\Laravel;

use Illuminate\Database\Eloquent\Model;

/**
 * A tiny Eloquent model. The @property tag is what lets Larastan tell a real
 * column from a typo: plain PHPStan sees only Eloquent's __get and cannot.
 *
 * @property string $body
 */
class Greeting extends Model
{
}
