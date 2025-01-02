<?php

namespace Spatie\LaravelData\Tests\Fakes\PropertyMorphableData;

use Spatie\LaravelData\Contracts\PropertyMorphableData;
use Spatie\LaravelData\Data;

abstract class AbstractPropertyMorphableData extends Data implements PropertyMorphableData
{
    public function __construct(
        public PropertyMorphableEnum $variant,
    ) {
    }

    public static function morph(...$payloads): ?string
    {
        $variant = $payloads[0]['variant'] ?? null;
        if (! $variant instanceof PropertyMorphableEnum) {
            $variant = PropertyMorphableEnum::tryFrom($variant);
        }

        return match ($variant) {
            PropertyMorphableEnum::A => PropertyMorphableDataA::class,
            PropertyMorphableEnum::B => PropertyMorphableDataB::class,
            default => null,
        };
    }
}
