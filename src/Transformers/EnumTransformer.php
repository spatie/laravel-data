<?php

namespace Spatie\LaravelData\Transformers;

use Spatie\LaravelData\Support\DataProperty;

class EnumTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value): string
    {
        /** @var \BackedEnum $value */
        return $value->value;
    }
}
