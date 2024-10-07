<?php

namespace Spatie\LaravelData\Attributes\FromData;

use Attribute;
use Illuminate\Http\Request;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FromCurrentUserProperty implements FromDataAttribute
{
    public function __construct(
        public ?string $guard = null,
        public ?string $property = null,
        public bool $replaceWhenPresentInBody = true,
        public ?string $userClass = null
    ) {
    }

    public function resolve(
        DataProperty $dataProperty,
        mixed $payload,
        array $properties,
        CreationContext $creationContext
    ): mixed {
        if (! $payload instanceof Request) {
            return null;
        }

        $fromCurrentUser = new FromCurrentUser($this->guard, $this->replaceWhenPresentInBody, $this->userClass);
        $user = $fromCurrentUser->resolve($dataProperty, $payload, $properties, $creationContext);
        if ($user === null) {
            return null;
        }

        return data_get($user, $this->property ?? $dataProperty->name);
    }
}
