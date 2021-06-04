<?php

namespace Spatie\LaravelData\Transformers;

use Spatie\LaravelData\Data;

class DataTransformer implements Transformer
{
    public function canTransform(mixed $value): bool
    {
        return $value instanceof Data;
    }

    public function transform(mixed $value, array $includes, array $excludes): mixed
    {
        /** @var \Spatie\LaravelData\Data $value */
        return $value->include(...$includes)->exclude(...$excludes)->toArray();
    }
}
