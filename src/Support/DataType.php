<?php

namespace Spatie\LaravelData\Support;

use Countable;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Enums\DataCollectableType;
use Spatie\LaravelData\Exceptions\CannotFindDataClass;
use Spatie\LaravelData\Exceptions\InvalidDataType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;
use TypeError;

class DataType implements Countable
{
    public readonly bool $isNullable;

    public readonly bool $isMixed;

    /** @deprecated will be removed in v4, check lazyType for a more correct check */
    public readonly bool $isLazy;

    public readonly bool $isOptional;

    public readonly bool $isDataObject;

    public readonly bool $isDataCollectable;

    public readonly ?DataCollectableType $dataCollectableType;

    /** @var class-string<BaseData>|null */
    public readonly ?string $dataClass;

    public readonly array $acceptedTypes;

    public readonly ?string $lazyType;

    public static function create(ReflectionParameter|ReflectionProperty $reflection): self
    {
        return new self($reflection);
    }

    public function __construct(ReflectionParameter|ReflectionProperty $reflection)
    {
        $type = $reflection->getType();

        if ($type === null) {
            $this->acceptedTypes = [];
            $this->isNullable = true;
            $this->isMixed = true;
            $this->isLazy = false;
            $this->isOptional = false;
            $this->isDataObject = false;
            $this->isDataCollectable = false;
            $this->dataCollectableType = null;
            $this->dataClass = null;
            $this->lazyType = null;

            return;
        }

        if ($type instanceof ReflectionNamedType) {
            if (is_a($type->getName(), Lazy::class, true)) {
                throw InvalidDataType::onlyLazy($reflection);
            }

            if (is_a($type->getName(), Optional::class, true)) {
                throw InvalidDataType::onlyOptional($reflection);
            }

            $this->isNullable = $type->allowsNull();
            $this->isMixed = $type->getName() === 'mixed';
            $this->acceptedTypes = $this->isMixed
                ? []
                : [
                    $type->getName() => $this->resolveBaseTypes($type->getName()),
                ];
            $this->isLazy = false;
            $this->isOptional = false;
            $this->isDataObject = is_a($type->getName(), BaseData::class, true);
            $this->dataCollectableType = $this->resolveDataCollectableType($type);
            $this->isDataCollectable = $this->dataCollectableType !== null;
            $this->lazyType = null;

            $this->dataClass = match (true) {
                $this->isDataObject => $type->getName(),
                $this->isDataCollectable => $this->resolveDataCollectableClass($reflection),
                default => null
            };

            return;
        }

        if (! ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType)) {
            throw new TypeError('Invalid reflection type');
        }

        $acceptedTypes = [];
        $isNullable = false;
        $isMixed = false;
        $isLazy = false;
        $isOptional = false;
        $isDataObject = false;
        $dataCollectableType = null;
        $lazyType = null;

        foreach ($type->getTypes() as $namedType) {
            $namedTypeName = $namedType->getName();
            $namedTypeIsLazy = is_a($namedTypeName, Lazy::class, true);
            $namedTypeIsOptional = is_a($namedTypeName, Optional::class, true);

            if ($namedTypeName !== 'null' && ! $namedTypeIsLazy && ! $namedTypeIsOptional) {
                $acceptedTypes[$namedTypeName] = $this->resolveBaseTypes($namedTypeName);
            }

            if($namedTypeIsLazy) {
                $lazyType = $namedTypeName;
            }

            $isNullable = $isNullable || $namedType->allowsNull();
            $isMixed = $namedTypeName === 'mixed';
            $isLazy = $isLazy || $namedTypeIsLazy;
            $isOptional = $isOptional || $namedTypeIsOptional;
            $isDataObject = $isDataObject || is_a($namedTypeName, BaseData::class, true);
            $dataCollectableType = $dataCollectableType ?? $this->resolveDataCollectableType($namedType);
        }

        $this->acceptedTypes = $acceptedTypes;
        $this->isNullable = $isNullable;
        $this->isMixed = $isMixed;
        $this->isLazy = $isLazy;
        $this->isOptional = $isOptional;
        $this->isDataObject = $isDataObject;
        $this->dataCollectableType = $dataCollectableType;
        $this->isDataCollectable = $this->dataCollectableType !== null;
        $this->lazyType = $lazyType;

        if ($this->isDataObject && count($this->acceptedTypes) > 1) {
            throw InvalidDataType::unionWithData($reflection);
        }

        if ($this->isDataCollectable && count($this->acceptedTypes) > 1) {
            throw InvalidDataType::unionWithDataCollection($reflection);
        }

        $this->dataClass = match (true) {
            $this->isDataObject => array_key_first($acceptedTypes),
            $this->isDataCollectable => $this->resolveDataCollectableClass($reflection),
            default => null
        };
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

        foreach ([$type, ...$this->resolveBaseTypes($type)] as $givenType) {
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

    protected function resolveBaseTypes(string $type): array
    {
        if (! class_exists($type)) {
            return [];
        }

        return array_unique([
            ...array_values(class_parents($type)),
            ...array_values(class_implements($type)),
        ]);
    }

    protected function resolveDataCollectableClass(
        ReflectionProperty|ReflectionParameter $reflection,
    ): ?string {
        $attributes = $reflection->getAttributes(DataCollectionOf::class);

        if (! empty($attributes)) {
            return $attributes[0]->getArguments()[0];
        }

        if ($reflection instanceof ReflectionParameter) {
            return null;
        }

        $class = (new DataCollectionAnnotationReader())->getClass($reflection);

        if ($class === null) {
            throw CannotFindDataClass::wrongDataCollectionAnnotation(
                $reflection->class,
                $reflection->name
            );
        }

        return $class;
    }

    protected function resolveDataCollectableType(
        ReflectionNamedType $reflection,
    ): ?DataCollectableType {
        $className = $reflection->getName();

        return match (true) {
            is_a($className, DataCollection::class, true) => DataCollectableType::Default,
            is_a($className, PaginatedDataCollection::class, true) => DataCollectableType::Paginated,
            is_a($className, CursorPaginatedDataCollection::class, true) => DataCollectableType::CursorPaginated,
            default => null,
        };
    }
}
