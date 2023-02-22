<?php

namespace Spatie\LaravelData\Support\Annotations;

class DataCollectableAnnotation
{
    public function __construct(
        public string $dataClass,
        public ?string $collectionClass = null,
        public ?string $property = null,
    ) {
    }
}
