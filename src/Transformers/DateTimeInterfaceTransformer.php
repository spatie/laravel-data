<?php

namespace Spatie\LaravelData\Transformers;

use DateTimeZone;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\DataProperty;

class DateTimeInterfaceTransformer implements Transformer
{
    protected string $format;

    public function __construct(
        ?string $format = null,
        protected ?string $setTimeZone = null
    ) {
        [$this->format] = Arr::wrap($format ?? config('data.date_format'));
    }

    public function transform(DataProperty $property, mixed $value): string
    {
        /** @var \DateTimeInterface $value */
        if ($this->setTimeZone) {
            $value = (clone $value)->setTimezone(new DateTimeZone($this->setTimeZone));
        }

        return $value->format(ltrim($this->format, '!'));
    }
}
