<?php

namespace Spatie\LaravelData\Support\Creation\Actions;

use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Spatie\LaravelData\Attributes\InjectsPropertyValue;
use Spatie\LaravelData\Exceptions\CannotCreateAbstractClass;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Normalizers\Normalized\Normalized;
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
            $payloads = [[]];
        }

        $this->fillNode(
            $state,
            $this->dataConfig->getDataClass($creationContext->dataClass),
            $sources,
            $payloads
        );

        return $state;
    }

    /**
     * @param array<int, array|Normalized> $sources
     * @param array<int, mixed> $rawPayloads
     */
    protected function fillNode(
        ConstructionState $state,
        DataClass $dataClass,
        array $sources,
        array $rawPayloads
    ): void {
        $sources = $this->applyPrepareDataHooks($state, $dataClass->name, $sources, $rawPayloads);

        if ($dataClass->isAbstract && $dataClass->propertyMorphable) {
            $dataClass = $this->resolveMorphedDataClass($state, $dataClass, $sources);
        }

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
     * @param array<int, array|Normalized> $sources
     */
    protected function resolveMorphedDataClass(
        ConstructionState $state,
        DataClass $dataClass,
        array $sources
    ): DataClass {
        $morphProperties = [];

        foreach ($dataClass->properties as $property) {
            if (! $property->morphable) {
                continue;
            }

            [$value] = $this->readValue($state, $property, $sources);

            if (! $value instanceof UnknownProperty) {
                $morphProperties[$property->name] = $value;
            }
        }

        $morphedClass = $this->morphClassResolver->execute($dataClass, [$morphProperties]);

        if ($morphedClass === null) {
            throw CannotCreateAbstractClass::morphClassWasNotResolved(originalClass: $dataClass->name);
        }

        return $this->dataConfig->getDataClass($morphedClass);
    }

    /**
     * @param array<int, array|Normalized> $sources
     *
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

    /**
     * @param array<int, array|Normalized> $sources
     * @param array<int, mixed> $rawPayloads
     *
     * @return array<int, array|Normalized>
     */
    protected function applyPrepareDataHooks(
        ConstructionState $state,
        string $class,
        array $sources,
        array $rawPayloads
    ): array {
        if ($state->creationContext->prepareData === []) {
            return $sources;
        }

        foreach ($rawPayloads as $index => $rawPayload) {
            $value = $rawPayload;

            foreach ($state->creationContext->prepareData as $hook) {
                $value = $hook($value, $class, $state->dotPath());
            }

            if ($value !== $rawPayload) {
                $sources[$index] = SourceResolver::resolve($class, $value, $this->normalizers);
            }
        }

        return $sources;
    }

    /**
     * @param array<int, mixed> $rawPayloads
     */
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

        if ($value === null || (! is_array($value) && ! is_object($value) && ! is_string($value))) {
            $state->writeValue($originalKey, $value);

            return;
        }

        try {
            $source = SourceResolver::resolve($nestedClass, $value, $this->normalizers);
        } catch (CannotCreateData) {
            $state->writeValue($originalKey, $value);

            return;
        }

        $state->writeValue($originalKey, []);

        $state->enterProperty(
            $property->name,
            $originalKey === $property->name ? null : $originalKey
        );

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

        if ($value instanceof AbstractPaginator || $value instanceof AbstractCursorPaginator) {
            $state->writeValue($originalKey, $value);

            return;
        }

        if (! is_iterable($value)) {
            $state->writeValue($originalKey, $value);

            return;
        }

        $state->writeValue($originalKey, []);

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

            if ($item === null || (! is_array($item) && ! is_object($item) && ! is_string($item))) {
                $state->writeValue($index, $item);

                continue;
            }

            try {
                $source = SourceResolver::resolve($itemClass, $item, $this->normalizers);
            } catch (CannotCreateData) {
                $state->writeValue($index, $item);

                continue;
            }

            $state->enterItem($index);

            $this->fillNode($state, $itemDataClass, [$source], [$item]);

            $state->leave();
        }

        $state->leave();
    }
}
