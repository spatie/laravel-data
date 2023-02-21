<?php

namespace Spatie\LaravelData\Support\Types;

use ReflectionIntersectionType;
use ReflectionUnionType;

class IntersectionType extends MultiType
{
    public function acceptsType(string $type): bool
    {
        if ($this->isMixed) {
            return true;
        }

        foreach ($this->types as $subType) {
            if (! $subType->acceptsType($type)) {
                return false;
            }
        }

        return true;
    }

    public function findAcceptedTypeForBaseType(string $class): ?string
    {
        foreach ($this->types as $subType) {
            if ($subType->findAcceptedTypeForBaseType($class) === null) {
                return null;
            }
        }

        return $class;
    }
}
