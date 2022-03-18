<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;

class AuthorizedDataPipe extends DataPipe
{
    public function handle(mixed $initialValue, DataClass $class, Collection $properties): Collection
    {
        if (! $initialValue instanceof Request) {
            return $properties;
        }

        $this->ensureRequestIsAuthorized($class->name);

        return $properties;
    }

    private function ensureRequestIsAuthorized(string $class): void
    {
        /** @psalm-suppress UndefinedMethod */
        if (method_exists($class, 'authorize') && $class::authorize() === false) {
            throw new AuthorizationException();
        }
    }
}
