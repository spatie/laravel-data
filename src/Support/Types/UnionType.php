<?php

namespace Spatie\LaravelData\Support\Types;

class UnionType extends MultiType
{
    public function acceptsType(string $type): bool
    {
        if ($this->isMixed) {
            return true;
        }

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
