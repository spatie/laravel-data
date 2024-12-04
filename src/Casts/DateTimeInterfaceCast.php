<?php

namespace Spatie\LaravelData\Casts;

use DateTimeInterface;
use DateTimeZone;
use Spatie\LaravelData\Exceptions\CannotCastDate;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class DateTimeInterfaceCast implements Cast, IterableItemCast
{
    public function __construct(
        protected null|string|array $format = null,
        protected ?string $type = null,
        protected ?string $setTimeZone = null,
        protected ?string $timeZone = null
    ) {
    }

    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): DateTimeInterface|Uncastable
    {
        return $this->castValue($this->type ?? $property->type->type->findAcceptedTypeForBaseType(DateTimeInterface::class), $value);
    }

    public function castIterableItem(DataProperty $property, mixed $value, array $properties, CreationContext $context): DateTimeInterface|Uncastable
    {
        return $this->castValue($property->type->iterableItemType, $value);
    }

    protected function castValue(
        ?string $type,
        mixed $value,
    ): Uncastable|null|DateTimeInterface {
        $formats = collect($this->format ?? config('data.date_format'));

        if ($type === null) {
            return Uncastable::create();
        }

        // Truncate nanoseconds to microseconds (first 6 digits)
        // Input: 2024-12-02T16:20:15.969827247Z
        $value = preg_replace('/\.(\d{6})\d*Z$/', '.$1Z', $value);
        // Output: 2024-12-02T16:20:15.969827Z

        /** @var DateTimeInterface|null $datetime */
        $datetime = $formats
            ->map(fn (string $format) => rescue(fn () => $type::createFromFormat(
                $format,
                $value instanceof DateTimeInterface ? $value->format($format) : (string) $value,
                isset($this->timeZone) ? new DateTimeZone($this->timeZone) : null
            ), report: false))
            ->first(fn ($value) => (bool) $value);

        if (! $datetime) {
            throw CannotCastDate::create($formats->toArray(), $type, $value);
        }

        $this->setTimeZone ??= config('data.date_timezone');

        if ($this->setTimeZone) {
            return $datetime->setTimezone(new DateTimeZone($this->setTimeZone));
        }

        return $datetime;
    }
}
