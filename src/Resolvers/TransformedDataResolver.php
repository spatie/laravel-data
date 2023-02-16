<?php

namespace Spatie\LaravelData\Resolvers;

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
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\ArrayableTransformer;
use Spatie\LaravelData\Transformers\Transformer;
use TypeError;
use function Pest\Laravel\instance;

class TransformedDataResolver
{
    public function __construct(
        protected DataConfig $dataConfig
    ) {
    }

    public function execute(
        BaseData&TransformableData $data,
        TransformationContext $context,
    ): array {
        $transformed = $this->transform($data, $context);

        if ($data instanceof WrappableData && $context->wrapExecutionType->shouldExecute()) {
            $transformed = $data->getWrap()->wrap($transformed);
        }

        if ($data instanceof AppendableData) {
            $transformed = array_merge($transformed, $data->getAdditionalData());
        }

        return $transformed;
    }

    private function transform(BaseData&TransformableData $data, TransformationContext $context): array
    {
        return $this->dataConfig
            ->getDataClass($data::class)
            ->properties
            ->reduce(function (array $payload, DataProperty $property) use ($data, $context) {
                $name = $property->name;

                if (! $this->shouldIncludeProperty($name, $data->{$name}, $context)) {
                    return $payload;
                }

                $value = $this->resolvePropertyValue(
                    $property,
                    $data->{$name},
                    $context,
                );

                if ($context->mapPropertyNames && $property->outputMappedName) {
                    $name = $property->outputMappedName;
                }

                $payload[$name] = $value;

                return $payload;
            }, []);
    }

    protected function shouldIncludeProperty(
        string $name,
        mixed $value,
        TransformationContext $context
    ): bool {
        if ($value instanceof Optional) {
            return false;
        }

        if ($this->isPropertyHidden($name, $context)) {
            return false;
        }

        if (! $value instanceof Lazy) {
            return true;
        }

        if ($value instanceof RelationalLazy || $value instanceof ConditionalLazy) {
            return $value->shouldBeIncluded();
        }

        // Lazy excluded checks

        if ($context->lazyExcluded instanceof AllTreeNode) {
            return false;
        }

        if ($context->lazyExcluded instanceof PartialTreeNode && $context->lazyExcluded->hasField($name)) {
            return false;
        }

        // Lazy included checks

        if ($context->lazyIncluded instanceof AllTreeNode) {
            return true;
        }

        if ($value->isDefaultIncluded()) {
            return true;
        }

        return $context->lazyIncluded instanceof PartialTreeNode && $context->lazyIncluded->hasField($name);
    }

    protected function isPropertyHidden(
        string $name,
        TransformationContext $context
    ): bool {
        if ($context->except instanceof AllTreeNode) {
            return true;
        }

        if (
            $context->except instanceof PartialTreeNode
            && $context->except->hasField($name)
            && $context->except->getNested($name) instanceof ExcludedTreeNode
        ) {
            return true;
        }

        if ($context->except instanceof PartialTreeNode) {
            return false;
        }

        if ($context->only instanceof AllTreeNode) {
            return false;
        }

        if ($context->only instanceof PartialTreeNode && $context->only->hasField($name)) {
            return false;
        }

        if ($context->only instanceof PartialTreeNode || $context->only instanceof ExcludedTreeNode) {
            return true;
        }

        return false;
    }

    protected function resolvePropertyValue(
        DataProperty $property,
        mixed $value,
        TransformationContext $context,
    ): mixed {
        if ($value instanceof Lazy) {
            $value = $value->resolve();
        }

        if ($value === null) {
            return null;
        }

        $nextContext = $context->next($property->name);

        if (is_array($value) && ($nextContext->only instanceof AllTreeNode || $nextContext->only instanceof PartialTreeNode)) {
            return Arr::only($value, $nextContext->only->getFields());
        }

        if (is_array($value) && ($nextContext->except instanceof AllTreeNode || $nextContext->except instanceof PartialTreeNode)) {
            return Arr::except($value, $nextContext->except->getFields());
        }

        if ($transformer = $this->resolveTransformerForValue($property, $value, $nextContext)) {
            return $transformer->transform($property, $value);
        }

        if (
            $value instanceof BaseDataCollectable
            && $value instanceof TransformableData
            && $nextContext->transformValues
        ) {
            $wrapExecutionType = match ($context->wrapExecutionType){
                WrapExecutionType::Enabled => WrapExecutionType::Enabled,
                WrapExecutionType::Disabled => WrapExecutionType::Disabled,
                WrapExecutionType::TemporarilyDisabled => WrapExecutionType::Enabled
            };

            return $value->transform2($nextContext->setWrapExecutionType($wrapExecutionType));
        }

        if (
            $value instanceof BaseData
            && $value instanceof TransformableData
            && $nextContext->transformValues
        ) {
            $wrapExecutionType = match ($context->wrapExecutionType){
                WrapExecutionType::Enabled => WrapExecutionType::TemporarilyDisabled,
                WrapExecutionType::Disabled => WrapExecutionType::Disabled,
                WrapExecutionType::TemporarilyDisabled => WrapExecutionType::TemporarilyDisabled
            };

            return $value->transform2($nextContext->setWrapExecutionType($wrapExecutionType));
        }

        if (
            $property->type->isDataCollectable
            && is_iterable($value)
            && $nextContext->transformValues
        ) {
            $wrapExecutionType = match ($context->wrapExecutionType){
                WrapExecutionType::Enabled => WrapExecutionType::Enabled,
                WrapExecutionType::Disabled => WrapExecutionType::Disabled,
                WrapExecutionType::TemporarilyDisabled => WrapExecutionType::Enabled
            };

            return app(TransformedDataCollectionResolver::class)->execute(
                $value,
                $nextContext->setWrapExecutionType($wrapExecutionType)
            );
        }
        return $value;
    }

    protected function resolveTransformerForValue(
        DataProperty $property,
        mixed $value,
        TransformationContext $context,
    ): ?Transformer {
        if (! $context->transformValues) {
            return null;
        }

        $transformer = $property->transformer ?? $this->dataConfig->findGlobalTransformerForValue($value);

        $shouldUseDefaultDataTransformer = $transformer instanceof ArrayableTransformer
            && ($property->type->isDataObject || $property->type->isDataCollectable);

        if ($shouldUseDefaultDataTransformer) {
            return null;
        }

        return $transformer;
    }
}
