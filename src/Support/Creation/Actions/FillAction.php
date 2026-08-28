<?php

namespace Spatie\LaravelData\Support\Creation\Actions;

use Spatie\LaravelData\Attributes\InjectsPropertyValue;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Normalizers\Normalizer;
use Spatie\LaravelData\Resolvers\DataMorphClassResolver;
use Spatie\LaravelData\Support\Creation\ConstructionState;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\SourceReader;
use Spatie\LaravelData\Support\Creation\SourceResolver;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Skipped;

class FillAction
{
    /**
     * @param array<int, Normalizer> $normalizers
     */
    public function __construct(
        protected DataConfig $dataConfig,
        protected DataMorphClassResolver $morphClassResolver,
        protected array $normalizers,
    ) {
    }

    /**
     * @param array<int, mixed> $payloads
     */
    public function execute(CreationContext $creationContext, array $payloads): ConstructionState
    {
        $state = ConstructionState::create($creationContext, $creationContext->dataClass);

        $sources = [];

        foreach ($payloads as $payload) {
            $sources[] = SourceResolver::resolve($creationContext->dataClass, $payload, $this->normalizers);
        }

        if ($sources === []) {
            $sources = [[]];
        }

        $this->fillNode(
            $state,
            $this->dataConfig->getDataClass($creationContext->dataClass),
            $sources,
            $payloads
        );

        return $state;
    }

    protected function fillNode(
        ConstructionState $state,
        DataClass $dataClass,
        array $sources,
        array $rawPayloads
    ): void {
        $sources = $this->applyPrepareDataHooks($state, $dataClass->name, $sources);

        $state->setNodeClass($dataClass->name);

        foreach ($dataClass->properties as $property) {
            [$value, $originalKey] = $this->readValue($state, $property, $sources);

            if ($originalKey !== $property->name) {
                $state->recordMapping($property->name, $originalKey);
            }

            $value = $this->applyInjection($state, $property, $value, $rawPayloads);

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

    /**
     * @return array{0: mixed, 1: string}
     */
    protected function readValue(
        ConstructionState $state,
        DataProperty $property,
        array $sources
    ): array {
        $mappedKey = $state->creationContext->mapPropertyNames
            ? $property->inputMappedName
            : null;

        if ($mappedKey === $property->name) {
            $mappedKey = null;
        }

        foreach ($sources as $source) {
            if ($mappedKey !== null) {
                $value = SourceReader::read($source, $mappedKey, $property);

                if (! $value instanceof UnknownProperty) {
                    return [$value, $mappedKey];
                }
            }

            $value = SourceReader::read($source, $property->name, $property);

            if (! $value instanceof UnknownProperty) {
                return [$value, $property->name];
            }
        }

        return [UnknownProperty::create(), $mappedKey ?? $property->name];
    }

    protected function applyPrepareDataHooks(
        ConstructionState $state,
        string $class,
        array $sources
    ): array {
        if ($state->creationContext->prepareData === []) {
            return $sources;
        }

        foreach ($sources as $index => $source) {
            $value = $source;

            foreach ($state->creationContext->prepareData as $hook) {
                $value = $hook($value, $class, $state->dotPath());
            }

            $sources[$index] = SourceResolver::resolve($class, $value, $this->normalizers);
        }

        return $sources;
    }

    protected function applyInjection(
        ConstructionState $state,
        DataProperty $property,
        mixed $value,
        array $rawPayloads
    ): mixed {
        $attributes = $property->attributes->all(InjectsPropertyValue::class);

        if ($attributes === []) {
            return $value;
        }

        foreach ($attributes as $attribute) {
            if (! $attribute->shouldBeReplacedWhenPresentInPayload() && ! $value instanceof UnknownProperty) {
                continue;
            }

            foreach (($rawPayloads === [] ? [null] : $rawPayloads) as $rawPayload) {
                $resolved = $attribute->resolve(
                    $property,
                    $rawPayload,
                    $state->currentValues(),
                    $state->creationContext
                );

                if ($resolved === Skipped::create()) {
                    continue;
                }

                return $resolved;
            }
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

        $state->enterProperty(
            $property->name,
            $originalKey === $property->name ? null : $originalKey
        );

        $source = SourceResolver::resolve($nestedClass, $value, $this->normalizers);

        $this->fillNode(
            $state,
            $this->dataConfig->getDataClass($nestedClass),
            [$source],
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

        if (! is_iterable($value)) {
            $state->writeValue($originalKey, $value);

            return;
        }

        $state->enterProperty(
            $property->name,
            $originalKey === $property->name ? null : $originalKey
        );

        $state->setNodeClass($itemClass);

        $itemDataClass = $this->dataConfig->getDataClass($itemClass);

        foreach ($value as $index => $item) {
            if ($item instanceof $itemClass) {
                $state->writeValue($index, $item);

                continue;
            }

            $state->enterItem($index);

            $source = SourceResolver::resolve($itemClass, $item, $this->normalizers);

            $this->fillNode($state, $itemDataClass, [$source], [$item]);

            $state->leave();
        }

        $state->leave();
    }
}
