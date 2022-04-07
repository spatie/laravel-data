<?php

namespace Spatie\LaravelData\Support;

use Countable;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotFindDataClass;
use Spatie\LaravelData\Exceptions\InvalidDataType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Undefined;

class DataType implements Countable
{
    public readonly bool $isNullable;

    public readonly bool $isMixed;

    public readonly bool $isLazy;

    public readonly bool $isUndefinable;

    public readonly bool $isDataObject;

    public readonly bool $isDataCollection;

    public readonly ?string $dataClass;

    public readonly array $acceptedTypes;

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
            $this->isUndefinable = false;
            $this->isDataObject = false;
            $this->isDataCollection = false;
            $this->dataClass = null;

            return;
        }

        if ($type instanceof ReflectionNamedType) {
            if (is_a($type->getName(), Lazy::class, true)) {
                throw InvalidDataType::onlyLazy($reflection);
            }

            if (is_a($type->getName(), Undefined::class, true)) {
                throw InvalidDataType::onlyUndefined($reflection);
            }

            $this->isNullable = $type->allowsNull();
            $this->isMixed = $type->getName() === 'mixed';
            $this->acceptedTypes = $this->isMixed ? [] : [
                $type->getName() => $this->resolveBaseTypes($type->getName()),
            ];
            $this->isLazy = false;
            $this->isUndefinable = false;
            $this->isDataObject = is_a($type->getName(), Data::class, true);
            $this->isDataCollection = is_a($type->getName(), DataCollection::class, true);

            $this->dataClass = match (true) {
                $this->isDataObject => $type->getName(),
                $this->isDataCollection => $this->resolveDataCollectionClass($reflection),
                default => null
            };

            return;
        }

        $acceptedTypes = [];
        $isNullable = false;
        $isMixed = false;
        $isLazy = false;
        $isUndefinable = false;
        $isDataObject = false;
        $isDataCollection = false;

        foreach ($type->getTypes() as $namedType) {
            if (! in_array($namedType, ['null', Lazy::class, Undefined::class])) {
                $acceptedTypes[$namedType->getName()] = $this->resolveBaseTypes($namedType->getName());
            }

            $isNullable = $isNullable || $namedType->allowsNull();
            $isMixed = $namedType->getName() === 'mixed';
            $isLazy = $isLazy || is_a($namedType->getName(), Lazy::class, true);
            $isUndefinable = $isUndefinable || is_a($namedType->getName(), Undefined::class, true);
            $isDataObject = $isDataObject || is_a($namedType->getName(), Data::class, true);
            $isDataCollection = $isDataCollection || is_a($namedType->getName(), DataCollection::class, true);
        }

        $this->acceptedTypes = $acceptedTypes;
        $this->isNullable = $isNullable;
        $this->isMixed = $isMixed;
        $this->isLazy = $isLazy;
        $this->isUndefinable = $isUndefinable;
        $this->isDataObject = $isDataObject;
        $this->isDataCollection = $isDataCollection;

        if ($this->isDataObject && count($this->acceptedTypes) > 1) {
            throw InvalidDataType::unionWithData($reflection);
        }

        if ($this->isDataCollection && count($this->acceptedTypes) > 1) {
            throw InvalidDataType::unionWithDataCollection($reflection);
        }

        $this->dataClass = match (true) {
            $this->isDataObject => array_key_first($acceptedTypes),
            $this->isDataCollection => $this->resolveDataCollectionClass($reflection),
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

    private function resolveBaseTypes(string $type): array
    {
        if (! class_exists($type)) {
            return [];
        }

        return array_unique([
            ...array_values(class_parents($type)),
            ...array_values(class_implements($type)),
        ]);
    }

    private function resolveDataCollectionClass(
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
}
