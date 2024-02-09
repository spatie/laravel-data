<?php

namespace Spatie\LaravelData\Support;

/**
 * @template T
 */
class DataStructureProperty
{
    /** @var T */
    protected mixed $resolved;

    /**
     * @return T
     * @note Will only be called when cached and thus already resolved
     */
    public function resolve()
    {
        return $this->resolved;
    }

    /**
     * @param T $resolved
     */
    public function setResolved(mixed $resolved): void
    {
        $this->resolved = $resolved;
    }
}
