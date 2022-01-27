<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;

class AuthorizedPipe extends DataPipe
{
    public function execute(mixed $value, Collection $payload, DataClass $class): Collection
    {
        if (! $value instanceof Request) {
            return $payload;
        }

        if ($class->hasAuthorizationMethod()) {
            $this->ensureRequestIsAuthorized($class->name());
        }

        return $payload;
    }

    private function ensureRequestIsAuthorized(string $class): void
    {
        /** @psalm-suppress UndefinedMethod */
        // TODO: remove this with the next major release
        if (method_exists($class, 'authorized') && $class::authorized() === false) {
            throw new AuthorizationException();
        }

        /** @psalm-suppress UndefinedMethod */
        if (method_exists($class, 'authorize') && $class::authorize() === false) {
            throw new AuthorizationException();
        }
    }
}
