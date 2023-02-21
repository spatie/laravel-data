<?php

namespace Spatie\LaravelData\Support\Partials;

use Closure;

class PartialsDefinition
{
    /**
     * @param array<string, bool|Closure> $includes
     * @param array<string, bool|Closure> $excludes
     * @param array<string, bool|Closure> $only
     * @param array<string, bool|Closure> $except
     */
    public function __construct(
        public array $includes = [],
        public array $excludes = [],
        public array $only = [],
        public array $except = [],
    )
    {
    }
}
