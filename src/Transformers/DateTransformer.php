<?php

namespace Spatie\LaravelData\Transformers;

use DateTimeInterface;

class DateTransformer implements Transformer
{
    public function canTransform(mixed $value): bool
    {
        return $value instanceof DateTimeInterface;
    }

    public function transform(mixed $value): string
    {
        /** @var \DateTimeInterface $value */
        return $value->format('Y-m-d H:i:s');
    }
}
