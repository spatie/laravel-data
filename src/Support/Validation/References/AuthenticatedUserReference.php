<?php

namespace Spatie\LaravelData\Support\Validation\References;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class AuthenticatedUserReference implements ExternalReference
{
    public function __construct(
        public ?string $property = null,
        public ?string $guard = null,
    ) {
    }

    public function getValue(): ?Authenticatable
    {
        $user = Auth::guard($this->guard)->user();

        if (! $user instanceof Authenticatable) {
            return null;
        }

        if ($this->property === null) {
            return $user;
        }

        return data_get($user, $this->property);
    }
}
