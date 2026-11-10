<?php

declare(strict_types=1);

namespace App;

// The plain-PHP twin of the laravel-vue fixture's Greeting model: the same
// shape without Eloquent, so the raw preset proves it on a framework-free app.
final class Greeting
{
    public function __construct(
        public readonly string $body,
        private readonly int $replyCount = 0,
    ) {}

    public function replyCount(): int
    {
        return $this->replyCount;
    }

    public function withReply(): self
    {
        return new self($this->body, $this->replyCount + 1);
    }
}
