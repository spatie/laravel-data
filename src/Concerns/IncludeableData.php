<?php

namespace Spatie\LaravelData\Concerns;

use Closure;
use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\PartialTrees;

trait IncludeableData
{
    protected ?PartialTrees $_partialTrees = null;

    /** @var array<string, bool|Closure> */
    protected array $_includes = [];

    /** @var array<string, bool|Closure> */
    protected array $_excludes = [];

    /** @var array<string, bool|Closure> */
    protected array $_only = [];

    /** @var array<string, bool|Closure> */
    protected array $_except = [];

    public function withPartialTrees(PartialTrees $partialTrees): static
    {
        $this->_partialTrees = $partialTrees;

        return $this;
    }

    public function include(string ...$includes): static
    {
        foreach ($includes as $include) {
            $this->_includes[$include] = true;
        }

        return $this;
    }

    public function exclude(string ...$excludes): static
    {
        foreach ($excludes as $exclude) {
            $this->_excludes[$exclude] = true;
        }

        return $this;
    }

    public function only(string ...$only): static
    {
        foreach ($only as $onlyDefinition) {
            $this->_only[$onlyDefinition] = true;
        }

        return $this;
    }

    public function except(string ...$except): static
    {
        foreach ($except as $exceptDefinition) {
            $this->_except[$exceptDefinition] = true;
        }

        return $this;
    }

    public function includeWhen(string $include, bool|Closure $condition): static
    {
        $this->_includes[$include] = $condition;

        return $this;
    }

    public function excludeWhen(string $exclude, bool|Closure $condition): static
    {
        $this->_excludes[$exclude] = $condition;

        return $this;
    }

    public function onlyWhen(string $only, bool|Closure $condition): static
    {
        $this->_only[$only] = $condition;

        return $this;
    }

    public function exceptWhen(string $except, bool|Closure $condition): static
    {
        $this->_except[$except] = $condition;

        return $this;
    }

    protected function includeProperties(): array
    {
        return [];
    }

    protected function excludeProperties(): array
    {
        return [];
    }

    protected function onlyProperties(): array
    {
        return [];
    }

    protected function exceptProperties(): array
    {
        return [];
    }

    public function getPartialTrees(): PartialTrees
    {
        if ($this->_partialTrees) {
            return $this->_partialTrees;
        }

        $filter = fn (bool|null|Closure $condition, string $definition) => match (true) {
            is_bool($condition) => $condition,
            $condition === null => false,
            is_callable($condition) => $condition($this),
        };

        $includes = collect($this->_includes)->merge($this->includeProperties())->filter($filter)->keys()->all();
        $excludes = collect($this->_excludes)->merge($this->excludeProperties())->filter($filter)->keys()->all();
        $only = collect($this->_only)->merge($this->onlyProperties())->filter($filter)->keys()->all();
        $except = collect($this->_except)->merge($this->exceptProperties())->filter($filter)->keys()->all();

        $partialsParser = app(PartialsParser::class);

        return new PartialTrees(
            $partialsParser->execute($includes),
            $partialsParser->execute($excludes),
            $partialsParser->execute($only),
            $partialsParser->execute($except),
        );
    }
}
