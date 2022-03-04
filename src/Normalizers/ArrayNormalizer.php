<?php

namespace Spatie\LaravelData\Normalizers;

use Illuminate\Contracts\Support\Arrayable;

class ArrayNormalizer extends Normalizer
{
    public function normalize(mixed $value): ?array
    {
        if(! is_array($value)){
            return null;
        }

        return $value;
    }
}
