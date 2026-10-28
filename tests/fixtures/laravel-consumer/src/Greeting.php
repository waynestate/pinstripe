<?php

declare(strict_types=1);

namespace Pinstripe\Fixtures\LaravelConsumer;

final class Greeting
{
    public function appName(): string
    {
        return (string) config('app.name');
    }

    public function length(string $key): int
    {
        return strlen($this->find($key));
    }

    private function find(string $key): ?string
    {
        if ($key === 'missing') {
            return null;
        }

        return 'value';
    }
}
