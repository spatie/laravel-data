<?php

namespace Spatie\LaravelData\Transformers;

use Illuminate\Contracts\Support\Arrayable;

class ArrayableTransformer implements Transformer
{
    public function canTransform(mixed $value): bool
    {
        return $value instanceof Arrayable;
    }

    public function transform(mixed $value, array $includes, array $excludes): mixed
    {
        /** @var \Illuminate\Contracts\Support\Arrayable $value */
        return $value->toArray();
    }
}
