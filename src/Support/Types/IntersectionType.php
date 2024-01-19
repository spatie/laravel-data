<?php

namespace Spatie\LaravelData\Support\Types;

class IntersectionType extends CombinationType
{
    public function acceptsType(string $type): bool
    {
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
