<?php

namespace Spatie\LaravelData\Transformers;

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;

class EnumTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): string|int
    {
        return $value->value;
    }
}
