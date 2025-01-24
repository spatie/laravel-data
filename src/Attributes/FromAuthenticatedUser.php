<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Skipped;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FromAuthenticatedUser implements InjectsPropertyValue
{
    public function __construct(
        public ?string $guard = null,
        public bool $replaceWhenPresentInPayload = true
    ) {
    }

    public function resolve(
        DataProperty $dataProperty,
        mixed $payload,
        array $properties,
        CreationContext $creationContext
    ): mixed {
        $user = Auth::guard($this->guard)->user();

        if ($user === null) {
            return Skipped::create();
        }

        return $user;
    }

    public function shouldBeReplacedWhenPresentInPayload(): bool
    {
        return $this->replaceWhenPresentInPayload;
    }
}
