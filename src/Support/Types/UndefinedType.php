<?php

namespace Spatie\LaravelData\Support\Types;

class UndefinedType extends Type
{
    public function __construct()
    {
        parent::__construct(isNullable: true, isMixed: true);
    }

    public function acceptsType(string $type): bool
    {
        return true;
    }

    public function findAcceptedTypeForBaseType(string $class): ?string
    {
        return $class;
    }

    public function getAcceptedTypes(): array
    {
        return [];
    }
}
