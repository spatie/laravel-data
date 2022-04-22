<?php

namespace Spatie\LaravelData\Casts;

use DateTimeInterface;
use Spatie\LaravelData\Exceptions\CannotCastDate;
use Spatie\LaravelData\Support\DataProperty;

class DateTimeInterfaceCast implements Cast
{
    public function __construct(
        protected ?string $format = null,
        protected ?string $type = null
    ) {
    }

    public function cast(DataProperty $property, mixed $value): DateTimeInterface | Uncastable
    {
        $formats = collect($this->format ?? config('data.date_format'));
        $type = $this->type ?? $this->findType($property);

        if ($type === null) {
            return Uncastable::create();
        }

        $datetime = $formats
            ->map(fn (string $format) => rescue(fn () => $type::createFromFormat($format, $value)))
            ->first(fn ($value) => (bool) $value);

        if (! $datetime) {
            /** @psalm-suppress InvalidCast,InvalidArgument */
            throw CannotCastDate::create($formats->toArray(), $type, $value);
        }

        return $datetime;
    }

    protected function findType(DataProperty $property): ?string
    {
        foreach ($property->types()->all() as $type) {
            if (is_a($type, DateTimeInterface::class, true)) {
                return (string) $type;
            }
        }

        return null;
    }
}
