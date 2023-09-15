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
    ) {
    }

    public function merge(PartialsDefinition $other): self
    {
        $this->includes = array_merge($this->includes, $other->includes);
        $this->only = array_merge($this->only, $other->only);
        $this->excludes = array_merge($this->excludes, $other->excludes);
        $this->except = array_merge($this->except, $other->except);

        return $this;
    }
}
