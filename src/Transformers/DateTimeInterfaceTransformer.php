<?php

namespace Spatie\LaravelData\Transformers;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\DataProperty;

class DateTimeInterfaceTransformer implements Transformer
{
    public function __construct(protected ?string $format = null)
    {
    }

    public function transform(DataProperty $property, mixed $value): string
    {
        [$format] = Arr::wrap($this->format ?? config('data.date_format'));

        /** @var \DateTimeInterface $value */
        return $value->format($format);
    }
}
