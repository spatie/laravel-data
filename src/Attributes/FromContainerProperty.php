<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Spatie\LaravelData\Attributes\Concerns\ResolvesPropertyForInjectedValue;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FromContainerProperty extends FromContainer
{
    use ResolvesPropertyForInjectedValue;

    public function __construct(
        ?string $dependency,
        array $parameters = [],
        public ?string $property = null,
        bool $replaceWhenPresentInPayload = true,
    ) {
        parent::__construct($dependency, $parameters, $replaceWhenPresentInPayload);
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
