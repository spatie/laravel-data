<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Exception;
use Spatie\LaravelData\Casts\Cast;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class WithCast
{
    public array $arguments;

    public function __construct(
        /** @var class-string<\Spatie\LaravelData\Casts\Cast> */
        public string $castClass,
        mixed ...$arguments
    ) {
        if (! is_a($this->castClass, Cast::class, true)) {
            throw new Exception("Cast given is not a cast");
        }

        $this->arguments = $arguments;
    }

    public function get(): Cast
    {
        return new ($this->castClass)(...$this->arguments);
    }
}
