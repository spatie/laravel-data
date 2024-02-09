<?php

namespace Spatie\LaravelData\Support\Types;

class UnionType extends CombinationType
{
    public function acceptsType(string $type): bool
    {
        foreach ($this->types as $subType) {
            if ($subType->acceptsType($type)) {
                return true;
            }
        }

        return false;
    }

    public function findAcceptedTypeForBaseType(string $class): ?string
    {
        foreach ($this->types as $subType) {
            if ($found = $subType->findAcceptedTypeForBaseType($class)) {
                return $found;
            }
        }

        return null;
    }
}
