<?php

namespace Spatie\LaravelData\Casts;

use BackedEnum;
use Spatie\LaravelData\Exceptions\CannotCastEnum;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Throwable;

class EnumCast implements Cast
{
    public function __construct(
        protected ?string $type = null
    ) {
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): BackedEnum | Uncastable
    {
        $type = $this->type ?? $property->type->type->findAcceptedTypeForBaseType(BackedEnum::class);

        if ($type === null) {
            return Uncastable::create();
        }

        /** @var class-string<\BackedEnum> $type */
        try {
            return $type::from($value);
        } catch (Throwable $e) {
            throw CannotCastEnum::create($type, $value);
        }
    }
}
