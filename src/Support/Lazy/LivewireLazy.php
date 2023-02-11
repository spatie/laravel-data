<?php

namespace Spatie\LaravelData\Support\Lazy;

use Closure;
use Illuminate\Contracts\Support\Htmlable;

class LivewireLazy extends ConditionalLazy implements Htmlable
{
    public function __construct(
        Closure $value,
    ) {
        parent::__construct(fn () => true, $value);
    }

    public function resolve(): mixed
    {
        return ($this->value)();
    }

    public function toHtml(): mixed
    {
        return $this->resolve();
    }
}
