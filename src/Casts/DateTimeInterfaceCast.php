<?php

namespace Spatie\LaravelData\Casts;

use DateTimeInterface;
use Spatie\LaravelData\Exceptions\CannotCastDate;
use Spatie\LaravelData\Support\DataProperty;

class DateTimeInterfaceCast implements Cast
{
    public function __construct(
        protected null|string|array $format = null,
        protected ?string $type = null
    ) {
    }

    public function cast(DataProperty $property, mixed $value, array $context): DateTimeInterface | Uncastable
    {
        $formats = collect($this->format ?? config('data.date_format'));

        $type = $this->type ?? $property->type->findAcceptedTypeForBaseType(DateTimeInterface::class);

        if ($type === null) {
            return Uncastable::create();
        }

        $datetime = $formats
            ->map(fn (string $format) => rescue(fn () => $type::createFromFormat($format, $value), report: false))
            ->first(fn ($value) => (bool) $value);

        if (! $datetime) {
            throw CannotCastDate::create($formats->toArray(), $type, $value);
        }

        return $datetime;
    }
}
