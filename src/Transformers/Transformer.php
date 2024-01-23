<?php

namespace Spatie\LaravelData\Transformers;

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;

interface Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed;
}
