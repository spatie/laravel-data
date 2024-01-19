<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\Types\Type;

/**
 * @template T of Type
 */
class DataType
{
    /**
     * @param class-string<Lazy>|null $lazyType
     */
    public function __construct(
        public readonly Type $type,
        public readonly bool $isOptional,
        public readonly bool $isNullable,
        public readonly bool $isMixed,
        public readonly ?string $lazyType,
        // @note for now we have a one data type per type rule
        // Meaning a type can be a data object of some type, data collection of some type or something else
        // If we want to support multiple types in the future all we need to do is replace calls to these
        // properties and handle everything correctly
        public readonly DataTypeKind $kind,
        public readonly ?string $dataClass,
        public readonly ?string $dataCollectableClass,
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

        return $this->type->acceptsType($type);
    }
}
