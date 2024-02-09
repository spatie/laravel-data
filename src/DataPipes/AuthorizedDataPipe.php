<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;

class AuthorizedDataPipe implements DataPipe
{
    public function handle(
        mixed $payload,
        DataClass $class,
        array $properties,
        CreationContext $creationContext
    ): array {
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
