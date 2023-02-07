<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Exceptions\CannotCreateCastAttribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class WithCastable extends WithCast
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
        $castType = $this->castableClass::castUsing(...$this->arguments);

        if (is_object($castType)) {
            return $castType;
        }

        return new $castType(...$this->arguments);
    }
}
