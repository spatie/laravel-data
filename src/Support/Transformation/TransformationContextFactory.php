<?php

namespace Spatie\LaravelData\Support\Transformation;

use Closure;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Resolvers\TransformedDataResolver;
use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

class TransformationContextFactory
{
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param bool $transformValues
     * @param bool $mapPropertyNames
     * @param \Spatie\LaravelData\Support\Wrapping\WrapExecutionType $wrapExecutionType
     * @param array<string, bool|Closure> $includes
     * @param array<string, bool|Closure> $excludes
     * @param array<string, bool|Closure> $only
     * @param array<string, bool|Closure> $except
     */
    protected function __construct(
        public bool $transformValues = true,
        public bool $mapPropertyNames = true,
        public WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        public array $includes = [],
        public array $excludes = [],
        public array $only = [],
        public array $except = [],
    ) {
    }

    public function get(): TransformationContext
    {
        return new TransformationContext(
            $this->transformValues,
            $this->mapPropertyNames,
            $this->wrapExecutionType,
            app(PartialsParser::class)->execute($this->includes),
            app(PartialsParser::class)->execute($this->excludes),
            app(PartialsParser::class)->execute($this->only),
            app(PartialsParser::class)->execute($this->except),
        );
    }

    public function transformValues(bool $transformValues = true): static
    {
        $this->transformValues = $transformValues;

        return $this;
    }

    public function mapPropertyNames(bool $mapPropertyNames = true): static
    {
        $this->mapPropertyNames = $mapPropertyNames;

        return $this;
    }

    public function wrapExecutionType(WrapExecutionType $wrapExecutionType): static
    {
        $this->wrapExecutionType = $wrapExecutionType;

        return $this;
    }

    public function include(string ...$includes): static
    {
        foreach ($includes as $include) {
            $this->includes[$include] = true;
        }

        return $this;
    }

    public function exclude(string ...$excludes): static
    {
        foreach ($excludes as $exclude) {
            $this->excludes[$exclude] = true;
        }

        return $this;
    }

    public function only(string ...$only): static
    {
        foreach ($only as $onlyDefinition) {
            $this->only[$onlyDefinition] = true;
        }

        return $this;
    }

    public function except(string ...$except): static
    {
        foreach ($except as $exceptDefinition) {
            $this->except[$exceptDefinition] = true;
        }

        return $this;
    }

    public function includeWhen(string $include, bool|Closure $condition): static
    {
        $this->includes[$include] = $condition;

        return $this;
    }

    public function excludeWhen(string $exclude, bool|Closure $condition): static
    {
        $this->excludes[$exclude] = $condition;

        return $this;
    }

    public function onlyWhen(string $only, bool|Closure $condition): static
    {
        $this->only[$only] = $condition;

        return $this;
    }

    public function exceptWhen(string $except, bool|Closure $condition): static
    {
        $this->except[$except] = $condition;

        return $this;
    }

    public function mergeDataContext(DataContext $context): static
    {
        $this->includes = array_merge($this->includes, $context->includes);
        $this->excludes = array_merge($this->excludes, $context->excludes);
        $this->only = array_merge($this->only, $context->only);
        $this->except = array_merge($this->includes, $context->except);

        return $this;
    }
}
