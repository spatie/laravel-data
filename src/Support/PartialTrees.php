<?php

namespace Spatie\LaravelData\Support;

class PartialTrees
{
    public function __construct(
        public ?array $lazyIncluded,
        public ?array $lazyExcluded,
        public ?array $only,
        public ?array $except,
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
