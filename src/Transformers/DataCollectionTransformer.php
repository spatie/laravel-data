<?php

namespace Spatie\LaravelData\Transformers;

use Spatie\LaravelData\DataCollection;

class DataCollectionTransformer implements Transformer
{
    public function canTransform(mixed $value): bool
    {
        return $value instanceof DataCollection;
    }

    public function transform(mixed $value, array $includes, array $excludes): mixed
    {
        /** @var \Spatie\LaravelData\DataCollection $value */
        return $value->include(...$includes)->exclude(...$excludes)->toArray();
    }
}
