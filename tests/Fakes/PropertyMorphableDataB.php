<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Tests\Fakes\Enums\PropertyMorphableEnum;

class PropertyMorphableDataB extends AbstractPropertyMorphableData
{
    public function __construct(
        public string $b,
    ) {
        parent::__construct(PropertyMorphableEnum::B);
    }
}
