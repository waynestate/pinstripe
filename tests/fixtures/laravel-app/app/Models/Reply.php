<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reply extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['body'];

    /**
     * @return BelongsTo<Greeting, $this>
     */
    public function greeting(): BelongsTo
    {
        return $this->belongsTo(Greeting::class, 'greeting_id');
    }
}
