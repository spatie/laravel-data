<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Support\Types\Type;

class DataType
{
    public function __construct(
        public readonly Type $type,
        public readonly bool $isNullable,
        public readonly bool $isMixed,
        public readonly DataTypeKind $kind,
    ) {
    }

    public function findAcceptedTypeForBaseType(string $class): ?string
    {
        return $this->type->findAcceptedTypeForBaseType($class);
    }

    public function acceptsType(string $type): bool
    {
        if ($this->isMixed) {
            return true;
        }

        return $this->type->acceptsType($type);
    }

    public function getAcceptedTypes(): array
    {
        if($this->isMixed) {
            return [];
        }

        return $this->type->getAcceptedTypes();
    }

    public function acceptsValue(mixed $value): bool
    {
        if ($this->isMixed) {
            return true;
        }

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
            $attributeArgumentKey = array_key_first($attributes[0]->getArguments());

            return $attributes[0]->getArguments()[$attributeArgumentKey];
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

        return $this->type->acceptsType($type);
    }
}
