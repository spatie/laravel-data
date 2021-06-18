<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Support\PartialsParser;

trait IncludeableData
{
    protected array $includes = [];

    protected array $excludes = [];

    protected ?array $inclusionTree = null;

    protected ?array $exclusionTree = null;

    public function withPartialsTrees(
        array $inclusionTree,
        array $exclusionTree
    ): static
    {
        $this->inclusionTree = $inclusionTree;
        $this->exclusionTree = $exclusionTree;

        return $this;
    }

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

    public function getInclusionTree(): array
    {
        return $this->inclusionTree ?? (new PartialsParser())->execute($this->includes);
    }

    public function getExclusionTree(): array
    {
        return $this->exclusionTree ?? (new PartialsParser())->execute($this->excludes);
    }
}
