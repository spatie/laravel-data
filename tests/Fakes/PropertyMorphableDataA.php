<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\Enums\PropertyMorphableEnum;

class PropertyMorphableDataA extends AbstractPropertyMorphableData
{
    public function __construct(
        public string $a,
        public DummyBackedEnum $enum,
    ) {
        parent::__construct(PropertyMorphableEnum::A);
    }
}
