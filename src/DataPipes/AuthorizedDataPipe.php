<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;

class AuthorizedDataPipe implements DataPipe
{
    public function handle(mixed $payload, DataClass $class, Collection $properties): Collection
    {
        if (! $payload instanceof Request) {
            return $properties;
        }

        $this->ensureRequestIsAuthorized($class->name);

        return $properties;
    }

    protected function ensureRequestIsAuthorized(string $class): void
    {
        /** @psalm-suppress UndefinedMethod */
        if (method_exists($class, 'authorize') && $class::authorize() === false) {
            throw new AuthorizationException();
        }
    }
}
