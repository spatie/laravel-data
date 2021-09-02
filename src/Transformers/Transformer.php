<?php

namespace Spatie\LaravelData\Transformers;

use Spatie\LaravelData\Support\DataProperty;

interface Transformer
{
    public function transform(DataProperty $property, mixed $value): mixed;
}
