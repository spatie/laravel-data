<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\PropertyForMorph;
use Spatie\LaravelData\Contracts\PropertyMorphableData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\Enums\PropertyMorphableEnum;

abstract class AbstractPropertyMorphableData extends Data implements PropertyMorphableData
{
    public function __construct(
        #[PropertyForMorph]
        public PropertyMorphableEnum $variant,
    ) {
    }

    public static function morph(array $properties): ?string
    {
        return match ($properties['variant'] ?? null) {
            PropertyMorphableEnum::A => PropertyMorphableDataA::class,
            PropertyMorphableEnum::B => PropertyMorphableDataB::class,
            default => null,
        };
    }
}
