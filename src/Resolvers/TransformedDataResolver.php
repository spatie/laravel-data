<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Contracts\AppendableData;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Contracts\WrappableData;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
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
    public function __construct(
        protected DataConfig $dataConfig,
        protected VisibleDataFieldsResolver $visibleDataFieldsResolver,
    ) {
    }

    public function execute(
        BaseData&TransformableData $data,
        TransformationContext $context,
    ): array {
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
        ) {
            $value = $this->transformIterableItems($property, $value, $currentContext);
        }

        if (is_array($value) && ! $property->type->kind->isDataCollectable()) {
            return $this->resolvePotentialPartialArray($value, $fieldContext);
        }

        if ($property->type->kind->isNonDataRelated()) {
            return $value; // Done for performance reasons
        }

        if (
            $value instanceof BaseDataCollectable
            && $value instanceof TransformableData
            && $currentContext->transformValues
        ) {
            $wrapExecutionType = match ($currentContext->wrapExecutionType) {
                WrapExecutionType::Enabled => WrapExecutionType::Enabled,
                WrapExecutionType::Disabled => WrapExecutionType::Disabled,
                WrapExecutionType::TemporarilyDisabled => WrapExecutionType::Enabled
            };

            $context = clone $fieldContext->setWrapExecutionType($wrapExecutionType);

            $transformed = $value->transform($context);

            $context->rollBackPartialsWhenRequired();

            return $transformed;
        }

        if (
            $value instanceof BaseData
            && $value instanceof TransformableData
            && $currentContext->transformValues
        ) {
            $wrapExecutionType = match ($currentContext->wrapExecutionType) {
                WrapExecutionType::Enabled => WrapExecutionType::TemporarilyDisabled,
                WrapExecutionType::Disabled => WrapExecutionType::Disabled,
                WrapExecutionType::TemporarilyDisabled => WrapExecutionType::TemporarilyDisabled
            };

            $context = clone $fieldContext->setWrapExecutionType($wrapExecutionType);

            $transformed = $value->transform($context);

            $context->rollBackPartialsWhenRequired();

            return $transformed;
        }

        if (
            $property->type->kind->isDataCollectable()
            && is_iterable($value)
            && $currentContext->transformValues
        ) {
            $wrapExecutionType = match ($currentContext->wrapExecutionType) {
                WrapExecutionType::Enabled => WrapExecutionType::Enabled,
                WrapExecutionType::Disabled => WrapExecutionType::Disabled,
                WrapExecutionType::TemporarilyDisabled => WrapExecutionType::Enabled
            };

            return DataContainer::get()->transformedDataCollectableResolver()->execute(
                $value,
                $fieldContext->setWrapExecutionType($wrapExecutionType)
            );
        }

        return $value;
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
        TransformationContext $fieldContext,
    ): array {
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
