<?php

namespace Spatie\LaravelData\Transformers;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;

class ArrayableTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): array
    {
        /** @var \Illuminate\Contracts\Support\Arrayable $value */
        return $value->toArray();
    }
}
