<?php

namespace Spatie\LaravelData;

use Closure;

class Lazy
{
    private function __construct(
        private Closure $closure
    ) {
    }

    public static function create(Closure $closure): self
    {
        return new self($closure);
    }

    public function resolve(): mixed
    {
        return ($this->closure)();
    }
}
