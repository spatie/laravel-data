<?php

namespace Spatie\LaravelData\Support\Types;

use ReflectionIntersectionType;
use ReflectionUnionType;

/**
 * @property \Spatie\LaravelData\Support\Types\PartialType[] $types
 */
abstract class MultiType extends Type
{
    public function __construct(
        bool $isNullable,
        bool $isMixed,
        public readonly array $types,
    ) {
        parent::__construct($isNullable, $isMixed);
    }

    public static function create(
        ReflectionUnionType|ReflectionIntersectionType $multiType,
        ?string $class,
    ): static {
        $isNullable = $multiType->allowsNull();
        $isMixed = false;
        $types = [];

        foreach ($multiType->getTypes() as $type) {
            if ($type->getName() === 'null') {
                continue;
            }

            if ($type->getName() === 'mixed') {
                $isMixed = true;
            }

            $types[] = PartialType::create($type, $class);
        }

        return new static(
            $isNullable,
            $isMixed,
            $types
        );
    }

    public function getAcceptedTypes(): array
    {
        $types = [];

        foreach ($this->types as $type) {
            $types[$type->name] = $type->acceptedTypes;
        }

        return $types;
    }

    public function acceptedTypesCount(): int
    {
        return count(array_filter(
            $this->types,
            fn (PartialType $subType) => ! $subType->isLazy() && ! $subType->isOptional()
        ));
    }
}
