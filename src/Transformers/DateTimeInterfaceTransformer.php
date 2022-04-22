<?php

namespace Spatie\LaravelData\Transformers;

use Spatie\LaravelData\Support\DataProperty;

class DateTimeInterfaceTransformer implements Transformer
{
    public function __construct(protected ?string $format = null)
    {
    }

    public function transform(DataProperty $property, mixed $value): string
    {
        return collect($this->format ?? config('data.date_format'))
            ->map(fn (string $format) => rescue(fn () => $value->format($format)))
            ->first(fn ($value) => (bool) $value, '');
    }
}
