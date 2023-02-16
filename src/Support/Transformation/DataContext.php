<?php

namespace Spatie\LaravelData\Support\Transformation;

use Closure;
use Spatie\LaravelData\Support\Wrapping\Wrap;

class DataContext
{
    /**
     * @param array<string, bool|Closure> $includes
     * @param array<string, bool|Closure> $excludes
     * @param array<string, bool|Closure> $only
     * @param array<string, bool|Closure> $except
     * @param \Spatie\LaravelData\Support\Wrapping\Wrap|null $wrap
     */
    public function __construct(
        public array $includes = [],
        public array $excludes = [],
        public array $only = [],
        public array $except = [],
        public ?Wrap $wrap = null,
    ) {
    }
}
