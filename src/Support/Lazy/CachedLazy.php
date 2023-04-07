<?php

namespace Spatie\LaravelData\Support\Lazy;

use Closure;
use Spatie\LaravelData\Lazy;

/**
 * @internal
 * @template T
 */
class CachedLazy extends Lazy
{
    /** @var T */
    protected mixed $resolved;

    /**
     * @param Closure(): T $value
     */
    public function __construct(
        protected Closure $value
    ) {
    }

    /**
     * @return T
     */
    public function resolve(): mixed
    {
        if (isset($this->resolved)) {
            return $this->resolved;
        }

        return $this->resolved = ($this->value)();
    }
}
