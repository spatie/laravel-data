<?php

namespace Spatie\LaravelData\Support\Types;

abstract class CombinationType extends Type
{
    /**
     * @param array<Type> $types
     */
    public function __construct(
        public readonly array $types,
    ) {
    }

    public function getAcceptedTypes(): array
    {
        $types = [];

        foreach ($this->types as $type) {
            foreach ($type->getAcceptedTypes() as $name => $acceptedTypes) {
                $types[$name] = $acceptedTypes;
            }
        }

        return $types;
    }

    public function isCreationContext(): bool
    {
        return false;
    }
}
