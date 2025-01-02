<?php

namespace Spatie\LaravelData\Tests\Fakes\PropertyMorphableData;

class PropertyMorphableDataA extends AbstractPropertyMorphableData
{
    public function __construct(
        public string $a,
    ) {
        parent::__construct('a');
    }
}
