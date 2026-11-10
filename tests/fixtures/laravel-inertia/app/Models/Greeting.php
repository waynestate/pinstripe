<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Greeting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['body'];

    /**
     * @return HasMany<Reply, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class, 'greeting_id');
    }

    public function replyCount(): int
    {
        return $this->replies()->count();
    }
}
