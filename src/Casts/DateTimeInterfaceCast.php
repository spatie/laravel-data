<?php

namespace Spatie\LaravelData\Casts;

use DateTimeInterface;
use ReflectionNamedType;
use ReflectionProperty;

class DateTimeInterfaceCast implements Cast
{
    public function cast(ReflectionNamedType $reflectionType, mixed $value): DateTimeInterface|Uncastable
    {
        $name = $reflectionType->getName();

        if(! is_a($name, DateTimeInterface::class, $value)){
            return Uncastable::create();
        }

        /** @var \DateTime|\DateTimeImmutable $name */
        return $name::createFromFormat('Y-m-d H:i:s', $value);
    }
}
