<?php

namespace Spatie\LaravelData\Transformers;

use Spatie\LaravelData\Support\DataProperty;

class ArrayableTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value): array
    {
        /** @var \Illuminate\Contracts\Support\Arrayable $value */
        return $value->toArray();
    }
}
