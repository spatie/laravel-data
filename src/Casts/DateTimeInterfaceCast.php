<?php

namespace Spatie\LaravelData\Casts;

use DateTimeInterface;
use Exception;
use Spatie\LaravelData\Support\DataProperty;

class DateTimeInterfaceCast implements Cast
{
    public function __construct(
        protected ?string $format = null
    ) {
    }

    public function cast(DataProperty $property, mixed $value): DateTimeInterface | Uncastable
    {
        $format = $this->format ?? config('data.date_format');

        $type = $this->findType($property);

        if ($type instanceof Uncastable) {
            return $type;
        }

        /** @var \DateTime|\DateTimeImmutable $name */
        $datetime = $type::createFromFormat($format, $value);

        if ($datetime === false) {
            throw new Exception("Could not cast date: `{$value}` using format {$format}");
        }

        return $datetime;
    }

    protected function findType(DataProperty $property)
    {
        foreach ($property->types() as $type) {
            if (is_a($type, DateTimeInterface::class, true)) {
                return $type;
            }
        }

        return Uncastable::create();
    }
}
