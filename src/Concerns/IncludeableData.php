<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\InclusionTrees;

trait IncludeableData
{
    protected ?InclusionTrees $inclusionTrees = null;

    protected array $includes = [];

    protected array $excludes = [];

    protected array $only = [];

    protected array $except = [];

    public function withInclusionTrees(
        InclusionTrees $inclusionTrees,
    ): static {
        $this->inclusionTrees = $inclusionTrees;

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

    public function only(string ...$only): static
    {
        $this->only = array_unique(array_merge($this->only, $only));

        return $this;
    }

    public function except(string ...$except): static
    {
        $this->except = array_unique(array_merge($this->except, $except));

        return $this;
    }

    public function getInclusionTrees(): InclusionTrees
    {
        if ($this->inclusionTrees) {
            return $this->inclusionTrees;
        }

        return new InclusionTrees(
            !empty($this->includes) ? (new PartialsParser())->execute($this->includes) : null,
            !empty($this->excludes) ? (new PartialsParser())->execute($this->excludes) : null,
            !empty($this->only) ? (new PartialsParser())->execute($this->only) : null,
            !empty($this->except) ? (new PartialsParser())->execute($this->except) : null,
        );
    }
}
