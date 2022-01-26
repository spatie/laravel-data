<?php

namespace Spatie\LaravelData\Tests\Fakes\Transformers;

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Transformers\Transformer;

class StringToUpperTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value): string
    {
        return strtoupper($value);
    }
};
