<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\PropertyTrees;

trait IncludeableData
{
    protected ?PropertyTrees $propertyTrees = null;

    protected array $includes = [];

    protected array $excludes = [];

    protected array $only = [];

    protected array $except = [];

    public function withPropertyTrees(
        PropertyTrees $propertyTrees,
    ): static {
        $this->propertyTrees = $propertyTrees;

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

    public function getPropertyTrees(): PropertyTrees
    {
        if ($this->propertyTrees) {
            return $this->propertyTrees;
        }

        return new PropertyTrees(
            (new PartialsParser())->execute($this->includes),
            (new PartialsParser())->execute($this->excludes),
            (new PartialsParser())->execute($this->only),
            (new PartialsParser())->execute($this->except),
        );
    }
}
