<?php

namespace Spatie\LaravelData;

trait IncludeableData
{
    private array $includes = [];

    private array $excludes = [];

    public function include(string ...$includes): static
    {
        $this->includes = array_unique(array_merge($this->includes, $includes));

        return $this;
    }

    public function exclude(string ...$excludes): static
    {
        $this->excludes = array_unique(array_merge($this->excludes, $excludes));

        return $this;
    }
}
