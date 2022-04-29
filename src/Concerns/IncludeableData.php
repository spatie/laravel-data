<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\PartialTrees;

trait IncludeableData
{
    protected ?PartialTrees $partialTrees = null;

    protected array $includes = [];

    protected array $excludes = [];

    protected array $only = [];

    protected array $except = [];

    public function withPartialTrees(
        PartialTrees $partialTrees,
    ): static {
        $this->partialTrees = $partialTrees;

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

    public function includeWhen(): array
    {
        return [];
    }

    public function excludeWhen(): array
    {
        return [];
    }

    public function getPartialTrees(): PartialTrees
    {
        if ($this->partialTrees) {
            return $this->partialTrees;
        }

        $only = $this->conditionalKeys($this->only, $this->includeWhen());
        $except = $this->conditionalKeys($this->except, $this->excludeWhen());

        return new PartialTrees(
            ! empty($this->includes) ? (new PartialsParser())->execute($this->includes) : null,
            ! empty($this->excludes) ? (new PartialsParser())->execute($this->excludes) : null,
            ! empty($only) ? (new PartialsParser())->execute($only) : null,
            ! empty($except) ? (new PartialsParser())->execute($except) : null,
        );
    }

    protected function conditionalKeys(array $array, array $conditions): array
    {
        return array_merge(array_keys(array_filter(
            $conditions,
            fn ($value) => $value instanceof \Closure
                ? ($value)($this)
                : $value
        )), $array);
    }
}
