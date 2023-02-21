<?php

namespace Spatie\LaravelData\Support\Types;

use Exception;
use ReflectionNamedType;

class SingleType extends Type
{
    public function __construct(
        bool $isNullable,
        bool $isMixed,
        public readonly PartialType $type,
    ) {
        parent::__construct(
            $isNullable,
            $isMixed
        );
    }

    public static function create(ReflectionNamedType $reflectionType, ?string $class): self
    {
        if ($reflectionType->getName() === 'null') {
            throw new Exception('Cannot create a single null type');
        }

        return new self(
            isNullable: $reflectionType->allowsNull(),
            isMixed: $reflectionType->getName() === 'mixed',
            type: PartialType::create($reflectionType, $class)
        );
    }

    public function acceptsType(string $type): bool
    {
        if ($this->isMixed) {
            return true;
        }

        return $this->type->acceptsType($type);
    }

    public function findAcceptedTypeForBaseType(string $class): ?string
    {
        return $this->type->findAcceptedTypeForBaseType($class);
    }

    public function getAcceptedTypes(): array
    {
        if($this->isMixed){
            return [];
        }

        return [
            $this->type->name => $this->type->acceptedTypes
        ];
    }
}
