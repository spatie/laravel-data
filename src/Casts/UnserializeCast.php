<?php

namespace Spatie\LaravelData\Casts;

use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class UnserializeCast implements Cast
{
    /**
     * @param bool $failSilently
     * @param array<int, class-string>|bool $allowedClasses
     */
    public function __construct(
        private readonly bool $failSilently = false,
        private readonly array|bool $allowedClasses = true,
    ) {
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        /** @var ?string $value */
        if ($value === null) {
            return null;
        }

        try {
            return unserialize($value, ['allowed_classes' => $this->allowedClasses]);
        } catch (\Throwable $e) {
            if ($this->failSilently) {
                return Uncastable::create();
            }

            throw $e;
        }
    }
}
