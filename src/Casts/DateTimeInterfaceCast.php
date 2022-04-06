<?php

namespace Spatie\LaravelData\Casts;

use DateTimeInterface;
use Spatie\LaravelData\Exceptions\CannotCastDate;
use Spatie\LaravelData\Support\DataProperty;
use Throwable;

class DateTimeInterfaceCast implements Cast
{
    public function __construct(
        protected ?string $format = null,
        protected ?string $type = null
    ) {
    }

    public function cast(DataProperty $property, mixed $value): DateTimeInterface | Uncastable
    {
        $format = $this->format ?? config('data.date_format');

        $type = $this->type ?? $this->findType($property);

        if ($type === null) {
            return Uncastable::create();
        }

        /** @var class-string<\DateTime|\DateTimeImmutable> $type */
        try {
            $datetime = $type::createFromFormat($format, $value);
        } catch (Throwable $e) {
            $datetime = false;
        }

        if ($datetime === false) {
            throw CannotCastDate::create($format, $type, $value);
        }

        return $datetime;
    }

    protected function findType(DataProperty $property): ?string
    {
        foreach ($property->types->all() as $type) {
            if (is_a($type, DateTimeInterface::class, true)) {
                return (string) $type;
            }
        }

        return null;
    }
}
