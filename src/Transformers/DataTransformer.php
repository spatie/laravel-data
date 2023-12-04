<?php

namespace Spatie\LaravelData\Transformers;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Contracts\AppendableData;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Contracts\WrappableData;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Lazy\ConditionalLazy;
use Spatie\LaravelData\Support\Lazy\RelationalLazy;
use Spatie\LaravelData\Support\PartialTrees;
use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use TypeError;

class DataTransformer
{
    protected DataConfig $config;

    public static function create(
        bool $transformValues,
        WrapExecutionType $wrapExecutionType,
        bool $mapPropertyNames,
    ): self {
        return new self($transformValues, $wrapExecutionType, $mapPropertyNames);
    }

    public function __construct(
        protected bool $transformValues,
        protected WrapExecutionType $wrapExecutionType,
        protected bool $mapPropertyNames,
    ) {
        $this->config = app(DataConfig::class);
    }

    /** @return array<mixed> */
    public function transform(TransformableData $data): array
    {
        $transformed = $this->resolvePayload($data);

        if ($data instanceof WrappableData && $this->wrapExecutionType->shouldExecute()) {
            $transformed = $data->getWrap()->wrap($transformed);
        }

        if ($data instanceof AppendableData) {
            $transformed = array_merge($transformed, $data->getAdditionalData());
        }

        return $transformed;
    }

    /** @return array<mixed> */
    protected function resolvePayload(TransformableData $data): array
    {
        $trees = $data instanceof IncludeableData
            ? $data->getPartialTrees()
            : new PartialTrees();

        $dataClass = $this->config->getDataClass($data::class);

        $payload = [];

        $objVars = get_object_vars($data);

        foreach ($dataClass->properties as $property) {
            if ($property->hidden) {
                continue;
            }

            $name = $property->name;

            if ($property->type->isOptional && ! array_key_exists($name, $objVars)) {
                continue;
            }

            if (! $this->shouldIncludeProperty($name, $data->{$name}, $trees)) {
                continue;
            }

            $value = $this->resolvePropertyValue(
                $property,
                $data->{$name},
                $trees->getNested($name),
            );

            if ($value instanceof Optional) {
                continue;
            }

            if ($this->mapPropertyNames && $property->outputMappedName) {
                $name = $property->outputMappedName;
            }

            $payload[$name] = $value;
        }

        return $payload;
    }

    protected function shouldIncludeProperty(
        string $name,
        mixed $value,
        PartialTrees $trees,
    ): bool {
        if ($value instanceof Optional) {
            return false;
        }

        if ($this->isPropertyHidden($name, $trees)) {
            return false;
        }

        if (! $value instanceof Lazy) {
            return true;
        }

        if ($value instanceof RelationalLazy || $value instanceof ConditionalLazy) {
            return $value->shouldBeIncluded();
        }

        if ($this->isPropertyLazyExcluded($name, $trees)) {
            return false;
        }

        return $this->isPropertyLazyIncluded($name, $value, $trees);
    }

    protected function isPropertyHidden(
        string $name,
        PartialTrees $trees,
    ): bool {
        if ($trees->except instanceof AllTreeNode) {
            return true;
        }

        if (
            $trees->except instanceof PartialTreeNode
            && $trees->except->hasField($name)
            && $trees->except->getNested($name) instanceof ExcludedTreeNode
        ) {
            return true;
        }

        if ($trees->except instanceof PartialTreeNode) {
            return false;
        }

        if ($trees->only instanceof AllTreeNode) {
            return false;
        }

        if ($trees->only instanceof PartialTreeNode && $trees->only->hasField($name)) {
            return false;
        }

        if ($trees->only instanceof PartialTreeNode || $trees->only instanceof ExcludedTreeNode) {
            return true;
        }

        return false;
    }

    protected function isPropertyLazyExcluded(
        string $name,
        PartialTrees $trees,
    ): bool {
        if ($trees->lazyExcluded instanceof AllTreeNode) {
            return true;
        }

        return $trees->lazyExcluded instanceof PartialTreeNode && $trees->lazyExcluded->hasField($name);
    }

    protected function isPropertyLazyIncluded(
        string $name,
        Lazy $value,
        PartialTrees $trees,
    ): bool {
        if ($trees->lazyIncluded instanceof AllTreeNode) {
            return true;
        }

        if ($value->isDefaultIncluded()) {
            return true;
        }

        return $trees->lazyIncluded instanceof PartialTreeNode && $trees->lazyIncluded->hasField($name);
    }

    protected function resolvePropertyValue(
        DataProperty $property,
        mixed $value,
        PartialTrees $trees
    ): mixed {
        if ($value instanceof Lazy) {
            $value = $value->resolve();
        }

        if ($value === null) {
            return null;
        }

        if (is_array($value) && ($trees->only instanceof AllTreeNode || $trees->only instanceof PartialTreeNode)) {
            $value = Arr::only($value, $trees->only->getFields());
        }

        if (is_array($value) && ($trees->except instanceof AllTreeNode || $trees->except instanceof PartialTreeNode)) {
            $value = Arr::except($value, $trees->except->getFields());
        }

        if ($transformer = $this->resolveTransformerForValue($property, $value)) {
            return $transformer->transform($property, $value);
        }

        if (! $value instanceof BaseData && ! $value instanceof BaseDataCollectable) {
            return $value;
        }

        if ($value instanceof IncludeableData) {
            $value->withPartialTrees($trees);
        }

        $wrapExecutionType = match (true) {
            $value instanceof BaseData && $this->wrapExecutionType === WrapExecutionType::Enabled => WrapExecutionType::TemporarilyDisabled,
            $value instanceof BaseData && $this->wrapExecutionType === WrapExecutionType::Disabled => WrapExecutionType::Disabled,
            $value instanceof BaseData && $this->wrapExecutionType === WrapExecutionType::TemporarilyDisabled => WrapExecutionType::TemporarilyDisabled,
            $value instanceof BaseDataCollectable && $this->wrapExecutionType === WrapExecutionType::Enabled => WrapExecutionType::Enabled,
            $value instanceof BaseDataCollectable && $this->wrapExecutionType === WrapExecutionType::Disabled => WrapExecutionType::Disabled,
            $value instanceof BaseDataCollectable && $this->wrapExecutionType === WrapExecutionType::TemporarilyDisabled => WrapExecutionType::Enabled,
            default => throw new TypeError('Invalid wrap execution type')
        };

        if ($value instanceof TransformableData && $this->transformValues) {
            return $value->transform($this->transformValues, $wrapExecutionType, $this->mapPropertyNames, );
        }

        return $value;
    }

    protected function resolveTransformerForValue(
        DataProperty $property,
        mixed $value,
    ): ?Transformer {
        if (! $this->transformValues) {
            return null;
        }

        $transformer = $property->transformer ?? $this->config->findGlobalTransformerForValue($value);

        $shouldUseDefaultDataTransformer = $transformer instanceof ArrayableTransformer
            && ($property->type->isDataObject || $property->type->isDataCollectable);

        if ($shouldUseDefaultDataTransformer) {
            return null;
        }

        return $transformer;
    }
}
