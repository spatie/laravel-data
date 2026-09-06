<?php

namespace Spatie\LaravelData\Casts;

use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class BuiltinTypeCast implements Cast, IterableItemCast
{
    public const TYPE_BOOL = 'bool';
    public const TYPE_INT = 'int';
    public const TYPE_FLOAT = 'float';
    public const TYPE_ARRAY = 'array';
    public const TYPE_STRING = 'string';

    public const SUPPORTED_TYPES = [
        self::TYPE_BOOL,
        self::TYPE_INT,
        self::TYPE_FLOAT,
        self::TYPE_ARRAY,
        self::TYPE_STRING,
    ];

    /**
     * @param self::TYPE_* $type
     */
    public function __construct(
        protected string $type,
    ) {
    }

    public static function supports(string $type): bool
    {
        return in_array($type, self::SUPPORTED_TYPES, true);
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        return $this->runCast($value);
    }

    public function castIterableItem(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        return $this->runCast($value);
    }

    protected function runCast(mixed $value): mixed
    {
        return match ($this->type) {
            self::TYPE_BOOL => $this->castToBool($value),
            self::TYPE_INT => (int) $value,
            self::TYPE_FLOAT => (float) $value,
            self::TYPE_ARRAY => (array) $value,
            self::TYPE_STRING => (string) $value,
        };
    }

    protected function castToBool(mixed $value): bool
    {
        if (! is_string($value)) {
            return (bool) $value;
        }

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            default => (bool) $value,
        };
    }
}
