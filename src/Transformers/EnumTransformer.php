<?php

namespace Spatie\LaravelData\Transformers;

use Spatie\LaravelData\Support\DataProperty;

class EnumTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, ?string $context = null): string|int
    {
        return $value->value;
    }
}
