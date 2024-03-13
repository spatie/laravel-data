<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Contracts\AppendableData;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Contracts\WrappableData;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Resolvers\Concerns\ChecksTransformationDepth;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataContainer;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\ArrayableTransformer;
use Spatie\LaravelData\Transformers\Transformer;

class TransformedDataResolver
{
    use ChecksTransformationDepth;

    public function __construct(
        protected DataConfig $dataConfig,
        protected VisibleDataFieldsResolver $visibleDataFieldsResolver,
    ) {
    }

    public function execute(
        BaseData&TransformableData $data,
        TransformationContext $context,
    ): array {
        if ($this->hasReachedMaxTransformationDepth($context)) {
            return $this->handleMaxDepthReached($context);
        }

        $dataClass = $this->dataConfig->getDataClass($data::class);

        $transformed = $this->transform($data, $context, $dataClass);

        if ($data instanceof WrappableData && $context->wrapExecutionType->shouldExecute()) {
            $transformed = $data->getWrap()->wrap($transformed);
        }

        if ($data instanceof AppendableData) {
            $transformed = array_merge($transformed, $data->getAdditionalData());
        }

        return $transformed;
    }

    private function transform(
        BaseData&TransformableData $data,
        TransformationContext $context,
        DataClass $dataClass,
    ): array {
        $payload = [];

        $visibleFields = $this->visibleDataFieldsResolver->execute($data, $dataClass, $context);

        foreach ($dataClass->properties as $property) {
            $name = $property->name;

            if (! array_key_exists($name, $visibleFields)) {
                continue;
            }

            $value = $this->resolvePropertyValue(
                $property,
                $data->{$name},
                $context,
                $visibleFields[$name] ?? null,
            );

            if ($value instanceof Optional) {
                continue;
            }

            if ($context->mapPropertyNames && $property->outputMappedName) {
                $name = $property->outputMappedName;
            }

            $payload[$name] = $value;
        }

        return $payload;
    }

    protected function resolvePropertyValue(
        DataProperty $property,
        mixed $value,
        TransformationContext $currentContext,
        ?TransformationContext $fieldContext
    ): mixed {
        if ($value instanceof Lazy) {
            $value = $value->resolve();
        }

        if ($value === null) {
            return null;
        }

        if ($transformer = $this->resolveTransformerForValue($property, $value, $currentContext)) {
            return $transformer->transform($property, $value, $currentContext);
        }

        if ($property->type->kind->isNonDataIteratable()
            && config('data.features.cast_and_transform_iterables')
            && is_iterable($value)
        ) {
            $value = $this->transformIterableItems($property, $value, $currentContext);
        }

        if (is_array($value) && ! $property->type->kind->isDataCollectable()) {
            return $this->resolvePotentialPartialArray($value, $fieldContext);
        }

        if ($property->type->kind->isNonDataRelated()) {
            return $value; // Done for performance reasons
        }

        if ($value instanceof TransformableData) {
            return $this->transformDataOrDataCollection($value, $currentContext, $fieldContext);
        }

        if ($property->type->kind->isDataCollectable() && is_iterable($value)) {
            $wrapExecutionType = match ($currentContext->wrapExecutionType) {
                WrapExecutionType::Enabled => WrapExecutionType::Enabled,
                WrapExecutionType::Disabled => WrapExecutionType::Disabled,
                WrapExecutionType::TemporarilyDisabled => WrapExecutionType::Enabled
            };

            $fieldContext = $fieldContext->setWrapExecutionType($wrapExecutionType);

            return DataContainer::get()->transformedDataCollectableResolver()->execute(
                $value,
                $fieldContext
            );
        }

        return $value;
    }

    protected function transformDataOrDataCollection(
        mixed $value,
        TransformationContext $currentContext,
        TransformationContext $fieldContext
    ): mixed {
        $wrapExecutionType = $this->resolveWrapExecutionType($value, $currentContext);

        $context = clone $fieldContext->setWrapExecutionType($wrapExecutionType);

        if ($context->transformValues === false && $context->hasPartials()) {
            $value->getDataContext()->mergeTransformationContext($context);

            return $value;
        }

        if ($context->transformValues === false) {
            return $value;
        }

        $transformed = $value->transform($context);

        $context->rollBackPartialsWhenRequired();

        return $transformed;
    }

    protected function resolveWrapExecutionType(
        TransformableData $value,
        TransformationContext $currentContext,
    ): WrapExecutionType {
        if ($currentContext->wrapExecutionType === WrapExecutionType::Disabled) {
            return WrapExecutionType::Disabled;
        }

        if ($value instanceof BaseData) {
            return WrapExecutionType::TemporarilyDisabled;
        }

        if ($currentContext->wrapExecutionType === WrapExecutionType::Enabled) {
            return WrapExecutionType::Enabled;
        }

        return WrapExecutionType::TemporarilyDisabled;
    }

    protected function transformIterableItems(
        DataProperty $property,
        iterable $items,
        TransformationContext $context,
    ): iterable {
        if (! $context->transformValues) {
            return $items;
        }

        /** @var Transformer|null $transformer */
        $transformer = null;

        foreach ($items as $key => $value) {
            if ($transformer === null) {
                $transformer = $this->resolveTransformerForValue($property, $value, $context);
            }

            if ($transformer === null) {
                return $items;
            }

            if ($transformer) {
                $items[$key] = $transformer->transform($property, $value, $context);
            }
        }

        return $items;
    }

    protected function resolvePotentialPartialArray(
        array $value,
        ?TransformationContext $fieldContext,
    ): array {
        if ($fieldContext === null) {
            return $value;
        }

        if ($fieldContext->exceptPartials && $fieldContext->exceptPartials->count() > 0) {
            $partials = [];

            foreach ($fieldContext->exceptPartials as $exceptPartial) {
                array_push($partials, ...$exceptPartial->toLaravel());
            }

            return Arr::except($value, $partials);
        }

        if ($fieldContext->onlyPartials && $fieldContext->onlyPartials->count() > 0) {
            $partials = [];

            foreach ($fieldContext->onlyPartials as $onlyPartial) {
                array_push($partials, ...$onlyPartial->toLaravel());
            }

            return Arr::only($value, $partials);
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

        $transformer = $property->transformer;

        if ($transformer === null && $context->transformers) {
            $transformer = $context->transformers->findTransformerForValue($value);
        }

        if ($transformer === null) {
            $transformer = $this->dataConfig->transformers->findTransformerForValue($value);
        }

        $shouldUseDefaultDataTransformer = $transformer instanceof ArrayableTransformer
            && $property->type->kind->isDataRelated();

        if ($shouldUseDefaultDataTransformer) {
            return null;
        }

        return $transformer;
    }
}
