<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Spatie\LaravelData\Attributes\Concerns\ResolvesPropertyForInjectedValue;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FromHeaderParameterProperty extends FromHeaderParameter
{
    use ResolvesPropertyForInjectedValue;

    public function __construct(
        string $headerParameter,
        public ?string $property = null,
        bool $replaceWhenPresentInPayload = true,
        /** @deprecated  */
        bool $replaceWhenPresentInBody = true
    ) {
        parent::__construct($headerParameter, $replaceWhenPresentInPayload, $replaceWhenPresentInBody);
    }

    public function resolve(
        DataProperty $dataProperty,
        mixed $payload,
        array $properties,
        CreationContext $creationContext
    ): mixed {
        return $this->resolvePropertyForInjectedValue(
            $dataProperty,
            $payload,
            $properties,
            $creationContext
        );
    }

    protected function getPropertyKey(): string|null
    {
        return $this->property;
    }
}
