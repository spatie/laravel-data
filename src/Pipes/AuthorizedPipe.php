<?php

namespace Spatie\LaravelData\Pipes;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataClass;

class AuthorizedPipe extends Pipe
{
    public function handle(mixed $initialValue, DataClass $class, Collection $properties): Collection|Data
    {
        if (! $initialValue instanceof Request) {
            return $properties;
        }

        if ($class->hasAuthorizationMethod()) {
            $this->ensureRequestIsAuthorized($class->name());
        }

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
