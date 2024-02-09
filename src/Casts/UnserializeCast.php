<?php

namespace Spatie\LaravelData\Casts;

use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class UnserializeCast implements Cast
{
    public function __construct(
        private bool $failSilently = false,
    ) {
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        /** @var ?string $value */
        if ($value === null) {
            return null;
        }

        try {
            return unserialize($value);
        } catch (\Throwable $e) {
            if($this->failSilently) {
                return Uncastable::create();
            }

            throw $e;
        }
    }
}
