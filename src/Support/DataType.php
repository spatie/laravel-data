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

        return $this->type->acceptsType($type);
    }
}
