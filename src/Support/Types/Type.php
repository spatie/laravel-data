<?php

namespace Spatie\LaravelData\Support\Types;

abstract class Type
{
    abstract public function acceptsType(string $type): bool;

    abstract public function findAcceptedTypeForBaseType(string $class): ?string;

    /**
     * @return array<string, array<string>>
     */
    abstract public function getAcceptedTypes(): array;

    abstract public function isCreationContext(): bool;
}
