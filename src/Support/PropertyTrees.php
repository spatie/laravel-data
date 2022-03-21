<?php

namespace Spatie\LaravelData\Support;

class PropertyTrees
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
            $this->lazyIncluded[$name] ?? [],
            $this->lazyExcluded[$name] ?? [],
            $this->only[$name] ?? [],
            $this->except[$name] ?? [],
        );
    }
}
