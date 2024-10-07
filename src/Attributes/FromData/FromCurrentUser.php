<?php

namespace Spatie\LaravelData\Attributes\FromData;

use Attribute;
use Illuminate\Http\Request;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FromCurrentUser implements FromDataAttribute
{
    /**
     * @param  class-string|null  $userClass
     */
    public function __construct(
        public ?string $guard = null,
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

        $name = $dataProperty->getInputName();
        if (! $this->replaceWhenPresentInBody && array_key_exists($name, $properties)) {
            return null;
        }

        $user = $payload->user($this->guard);

        if (
            $user === null
            || ($this->userClass && ! ($user instanceof $this->userClass))) {
            return null;
        }

        return $user;
    }
}
