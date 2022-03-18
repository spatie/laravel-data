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
}
