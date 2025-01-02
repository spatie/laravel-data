<?php

namespace Spatie\LaravelData\Tests\Fakes\PropertyMorphableData;

class PropertyMorphableDataB extends AbstractPropertyMorphableData
{
    public function __construct(
        public string $b,
    ) {
        parent::__construct(PropertyMorphableEnum::B);
    }
}
