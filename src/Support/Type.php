<?php

namespace Spatie\LaravelData\Support;

use Countable;
use Exception;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\PaginatedDataCollection;

class Type implements Countable
{
    public function __construct(
        public readonly bool $isNullable,
        public readonly bool $isMixed,
        public readonly array $acceptedTypes,
    ) {
    }

    public static function create(
        ?ReflectionType $typeReflection
    ) {
        if ($typeReflection === null) {
            return new self(
                isNullable: true,
                isMixed: true,
                acceptedTypes: [],
            );
        }

        if ($typeReflection instanceof ReflectionNamedType) {
            $hasAcceptedTypes = $typeReflection->getName() !== 'mixed' && $typeReflection->getName() !== 'null';

            return new self(
                isNullable: $typeReflection->allowsNull(),
                isMixed: $typeReflection->getName() === 'mixed',
                acceptedTypes: $hasAcceptedTypes
                    ? [$typeReflection->getName() => self::resolveBaseTypes($typeReflection->getName())]
                    : []
            );
        }

        if (! $typeReflection instanceof ReflectionUnionType && ! $typeReflection instanceof ReflectionIntersectionType) {
            throw new Exception('Cannot create type');
        }

        // TODO: basically rewrite of DataTypefactory, let's rewrite the whole type system

        $isNullable = false;
        $isMixed = false;
        $acceptedTypes = [];

        foreach ($typeReflection->getTypes() as $subTypeReflection) {
            $subType = static::create($subTypeReflection);

            $isNullable = $isNullable || $subType->isNullable;
            $isMixed = $isMixed || $subType->isMixed;
            $acceptedTypes = [...$acceptedTypes, ...$subType->acceptedTypes];
        }

        return new self(
            isNullable: $isNullable,
            isMixed: $isMixed,
            acceptedTypes: $acceptedTypes
        );
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function count(): int
    {
        return count($this->acceptedTypes);
    }

    public function acceptsValue(mixed $value): bool
    {
        if ($this->isNullable && $value === null) {
            return true;
        }

        $type = gettype($value);

        $type = match ($type) {
            'integer' => 'int',
            'boolean' => 'bool',
            'double' => 'float',
            'object' => $value::class,
            default => $type,
        };

        return $this->acceptsType($type);
    }


    public function acceptsType(string $type): bool
    {
        if ($this->isMixed) {
            return true;
        }

        if (array_key_exists($type, $this->acceptedTypes)) {
            return true;
        }

        if (in_array($type, ['string', 'int', 'bool', 'float', 'array'])) {
            return false;
        }

        $baseTypes = class_exists($type)
            ? array_unique([
                ...array_values(class_parents($type)),
                ...array_values(class_implements($type)),
            ])
            : [];

        foreach ([$type, ...$baseTypes] as $givenType) {
            if (array_key_exists($givenType, $this->acceptedTypes)) {
                return true;
            }
        }

        return false;
    }

    public function findAcceptedTypeForBaseType(string $class): ?string
    {
        foreach ($this->acceptedTypes as $acceptedType => $acceptedBaseTypes) {
            if ($class === $acceptedType) {
                return $acceptedType;
            }

            if (in_array($class, $acceptedBaseTypes)) {
                return $acceptedType;
            }
        }

        return null;
    }

    protected static function resolveBaseTypes(string $type): array
    {
        if (! class_exists($type)) {
            return [];
        }

        return array_unique([
            ...array_values(class_parents($type)),
            ...array_values(class_implements($type)),
        ]);
    }
}
