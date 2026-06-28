<?php

declare(strict_types=1);

namespace Spatie\LaravelData\Tests\Stubs;

use Spatie\LaravelData\Attributes\PropertyForMorph;
use Spatie\LaravelData\Contracts\PropertyMorphableData;
use Spatie\LaravelData\Data;

abstract class Person extends Data implements PropertyMorphableData
{
    #[PropertyForMorph]
    public readonly ArtistType $type;

    public readonly string $name;

    public static function morph(array $properties): ?string
    {
        return match ($properties['type']) {
            ArtistType::Singer => Singer::class,
            ArtistType::Musician => Musician::class,
        };
    }
}
