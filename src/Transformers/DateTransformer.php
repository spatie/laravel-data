<?php

namespace Spatie\LaravelData\Transformers;

use DateTimeInterface;

class DateTransformer implements Transformer
{
    public function __construct(protected ?string $format = null)
    {
    }

    public function canTransform(mixed $value): bool
    {
        return $value instanceof DateTimeInterface;
    }

    public function transform(mixed $value): string
    {
        $format = $this->format ?? config('data.date_format');

        /** @var \DateTimeInterface $value */
        return $value->format($format);
    }
}
