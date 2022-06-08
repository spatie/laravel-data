<?php

namespace Spatie\LaravelData\Support;

class PartialTrees
{
    public function __construct(
        public ?array $lazyIncluded = null,
        public ?array $lazyExcluded = null,
        public ?array $only = null,
        public ?array $except = null,
    ) {
    }

    public function getNested(string $name): self
    {
        return new self(
            $this->lazyIncluded[$name] ?? null,
            $this->lazyExcluded[$name] ?? null,
            $this->only[$name] ?? null,
            $this->except[$name] ?? null,
        );
    }
}
