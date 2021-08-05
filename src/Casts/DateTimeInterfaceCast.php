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
        $format = $this->format ?? config('data.date_format');

        $type = $this->type ?? $this->findType($property);

        if ($type === null) {
            return Uncastable::create();
        }

        /** @var \DateTime|\DateTimeImmutable $name */
        $datetime = $type::createFromFormat($format, $value);

        if ($datetime === false) {
            throw CannotCastDate::create($format, $type, $value);
        }

        return $datetime;
    }

    protected function findType(DataProperty $property): ?string
    {
        foreach ($property->types() as $type) {
            if (is_a($type, DateTimeInterface::class, true)) {
                return (string) $type;
            }
        }

        return null;
    }
}
