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

    public function cast(DataProperty $property, mixed $value, array $context): DateTimeInterface | Uncastable
    {
        $format = $this->format ?? config('data.date_format');

        $type = $this->type ?? $property->type->findAcceptedTypeForBaseType(DateTimeInterface::class);

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
}
