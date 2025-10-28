<?php

namespace Spatie\LaravelData\Casts;

use BackedEnum;
use Spatie\LaravelData\Exceptions\CannotCastEnum;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Throwable;

class EnumCast implements Cast, IterableItemCast
{
    public function __construct(
        protected ?string $type = null
    ) {
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): BackedEnum|Uncastable
    {
        return $this->castValue(
            $this->type ?? $property->type->type->findAcceptedTypeForBaseType(BackedEnum::class),
            $value,
            $property
        );
    }

    public function castIterableItem(DataProperty $property, mixed $value, array $properties, CreationContext $context): BackedEnum|Uncastable
    {
        return $this->castValue(
            $property->type->iterableItemType,
            $value,
            $property
        );
    }

    protected function castValue(
        ?string $type,
        mixed $value,
        DataProperty $property
    ): BackedEnum|Uncastable {
        if ($type === null) {
            return Uncastable::create();
        }

        if ($value instanceof $type) {
            return $value;
        }

        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        try {
            return $type::from($value);
        } catch (Throwable $e) {
            throw CannotCastEnum::create($type, $value, $property);
        }
    }
}
