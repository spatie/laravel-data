<?php

namespace Spatie\LaravelData\Support\Creation\Actions;

use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Spatie\LaravelData\Attributes\InjectsPropertyValue;
use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Normalizers\Normalized\UnNormalized;
use Spatie\LaravelData\Support\Creation\ConstructionState;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Skipped;

class FillAction
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected NormalizePayloadAction $normalizePayloadAction,
        protected ReadDataPropertyAction $readDataPropertyAction,
        protected ResolveMorphedDataClassAction $resolveMorphedDataClassAction,
    ) {
    }

    /**
     * @param array<int, mixed> $payloads
     */
    public function execute(CreationContext $creationContext, array $payloads): ConstructionState
    {
        $state = ConstructionState::create($creationContext, $creationContext->dataClass);

        $normalized = [];

        foreach ($payloads as $payload) {
            $normalized[] = $this->normalizePayloadAction->execute($payload);
        }

        $this->fillNode(
            $state,
            $this->dataConfig->getDataClass($creationContext->dataClass),
            $normalized,
            $payloads
        );

        return $state;
    }

    /**
     * @param array<int, array|Normalized> $normalized
     * @param array<int, mixed> $payloads
     */
    protected function fillNode(
        ConstructionState $state,
        DataClass $dataClass,
        array $normalized,
        array $payloads
    ): void {
        foreach ($state->creationContext->prepareDataHooks as $hook) {
            $normalized = array_values($hook($normalized, $dataClass->name, $state->dotPath(), $payloads));
        }

        if ($dataClass->isAbstract && $dataClass->propertyMorphable) {
            $dataClass = $this->resolveMorphedDataClassAction->execute(
                $state->creationContext,
                $dataClass,
                $normalized
            );
        }

        $state->setNodeClass($dataClass);

        foreach ($dataClass->properties as $property) {
            [$value, $originalKey] = $this->readDataPropertyAction->execute(
                $state->creationContext,
                $property,
                $normalized
            );

            if ($originalKey !== $property->name) {
                $state->recordMapping($property->name, $originalKey);
            }

            $value = $this->applyInjection($state, $property, $value);

            if ($value instanceof UnknownProperty) {
                continue;
            }

            if ($property->type->kind->isDataObject()) {
                $this->fillDataObjectProperty($state, $property, $value, $originalKey);

                continue;
            }

            if ($property->type->kind->isDataCollectable()) {
                $this->fillDataCollectionProperty($state, $property, $value, $originalKey);

                continue;
            }

            $state->writeValue($originalKey, $value);
        }
    }

    protected function applyInjection(
        ConstructionState $state,
        DataProperty $property,
        mixed $value
    ): mixed {
        $attributes = $property->attributes->all(InjectsPropertyValue::class);

        if ($attributes === []) {
            return $value;
        }

        foreach ($attributes as $attribute) {
            if (! $attribute->shouldBeReplacedWhenPresentInPayload() && ! $value instanceof UnknownProperty) {
                continue;
            }

            $resolved = $attribute->resolve($property, $state->creationContext);

            if ($resolved === Skipped::create()) {
                continue;
            }

            return $resolved;
        }

        return $value;
    }

    protected function fillDataObjectProperty(
        ConstructionState $state,
        DataProperty $property,
        mixed $value,
        string $originalKey
    ): void {
        /** @var class-string $nestedClass */
        $nestedClass = $property->type->dataClass;

        if ($value instanceof $nestedClass) {
            $state->writeValue($originalKey, $value);

            return;
        }

        if ($value === null || (! is_array($value) && ! is_object($value) && ! is_string($value))) {
            $state->writeValue($originalKey, $value);

            return;
        }

        $normalizedPayload = $this->normalizePayloadAction->execute($value);

        if ($normalizedPayload instanceof UnNormalized) {
            $state->writeValue($originalKey, $value);

            return;
        }

        $state->enterProperty($property->name, $originalKey);

        $this->fillNode(
            $state,
            $this->dataConfig->getDataClass($nestedClass),
            [$normalizedPayload],
            [$value]
        );

        $state->leave();
    }

    protected function fillDataCollectionProperty(
        ConstructionState $state,
        DataProperty $property,
        mixed $value,
        string $originalKey
    ): void {
        /** @var class-string $itemClass */
        $itemClass = $property->type->dataClass;

        if ($value instanceof AbstractPaginator || $value instanceof AbstractCursorPaginator) {
            $state->writeValue($originalKey, $value);

            return;
        }

        if (! is_iterable($value)) {
            $state->writeValue($originalKey, $value);

            return;
        }

        $state->enterProperty($property->name, $originalKey);

        $itemDataClass = $this->dataConfig->getDataClass($itemClass);

        $state->setNodeClass($itemDataClass);

        foreach ($value as $index => $item) {
            if ($item instanceof $itemClass) {
                $state->writeValue($index, $item);

                continue;
            }

            if ($item === null || (! is_array($item) && ! is_object($item) && ! is_string($item))) {
                $state->writeValue($index, $item);

                continue;
            }

            $normalizedPayload = $this->normalizePayloadAction->execute($item);

            if ($normalizedPayload instanceof UnNormalized) {
                $state->writeValue($index, $item);

                continue;
            }

            $state->enterItem($index);

            $this->fillNode($state, $itemDataClass, [$normalizedPayload], [$item]);

            $state->leave();
        }

        $state->leave();
    }
}
