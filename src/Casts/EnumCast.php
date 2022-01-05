<?php

namespace Spatie\LaravelData\Casts;

use BackedEnum;
use Spatie\LaravelData\Exceptions\CannotCastEnum;
use Spatie\LaravelData\Support\DataProperty;
use Throwable;

class EnumCast implements Cast
{
    public function __construct(
        protected ?string $type = null
    ) {
    }

    public function cast(DataProperty $property, mixed $value): BackedEnum | Uncastable
    {
        $type = $this->type ?? $this->findType($property);

        if ($type === null) {
            return Uncastable::create();
        }

        /** @var \BackedEnum $type */
        try {
            return $type::from($value);
        } catch (Throwable $e) {
            /** @psalm-suppress InvalidCast,InvalidArgument */
            throw CannotCastEnum::create($type, $value);
        }
    }

    protected function findType(DataProperty $property): ?string
    {
        foreach ($property->types()->all() as $type) {
            if (is_a($type, BackedEnum::class, true)) {
                return (string) $type;
            }
        }

        return null;
    }
}
