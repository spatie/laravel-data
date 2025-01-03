<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Casts\CastableCast;
use Spatie\LaravelData\Exceptions\CannotCreateCastAttribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class WithCastable implements GetsCast
{
    public array $arguments;

    public function __construct(
        /** @var class-string<\Spatie\LaravelData\Casts\Castable> $castableClass */
        public string $castableClass,
        mixed ...$arguments
    ) {
        if (! is_a($this->castableClass, Castable::class, true)) {
            throw CannotCreateCastAttribute::notACastable();
        }

        $this->arguments = $arguments;
    }

    public function get(): Cast
    {
        return new CastableCast(
            $this->castableClass,
            $this->arguments
        );
    }
}
