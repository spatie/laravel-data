<?php

namespace Spatie\LaravelData\Tests\Fakes\PropertyMorphableData;

use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Contracts\PropertyMorphableData;
use Spatie\LaravelData\Data;

abstract class AbstractPropertyMorphableData extends Data implements PropertyMorphableData
{
    public function __construct(
        #[In('a', 'b')]
        public string $variant,
    ) {
    }

    public static function morph(array $properties): ?string
    {
        return match ($properties['variant'] ?? null) {
            'a' => PropertyMorphableDataA::class,
            'b' => PropertyMorphableDataB::class,
            default => null,
        };
    }
}
