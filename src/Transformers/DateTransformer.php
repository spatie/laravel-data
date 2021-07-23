<?php

namespace Spatie\LaravelData\Transformers;

use DateTimeInterface;

class DateTransformer implements Transformer
{
    public function __construct(protected string $format)
    {
    }

    public function canTransform(mixed $value): bool
    {
        return $value instanceof DateTimeInterface;
    }

    public function transform(mixed $value): string
    {
        /** @var \DateTimeInterface $value */
        return $value->format($this->format);
    }
}
