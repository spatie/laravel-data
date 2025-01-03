<?php

namespace Spatie\LaravelData\Tests\Fakes\PropertyMorphableData;

use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;

class PropertyMorphableDataA extends AbstractPropertyMorphableData
{
    public function __construct(
        public string $a,
        public DummyBackedEnum $enum,
    ) {
        parent::__construct('a');
    }
}
