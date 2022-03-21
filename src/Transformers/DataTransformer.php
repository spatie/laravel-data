<?php

namespace Spatie\LaravelData\Transformers;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\InclusionTrees;
use Spatie\LaravelData\Support\Lazy\ConditionalLazy;
use Spatie\LaravelData\Support\Lazy\RelationalLazy;
use Spatie\LaravelData\Support\TransformationType;
use Spatie\LaravelData\Undefined;

class DataTransformer
{
    private DataConfig $config;

    public static function create(TransformationType $transformationType): self
    {
        return new self($transformationType);
    }

    public function __construct(protected TransformationType $transformationType)
    {
        $this->config = app(DataConfig::class);
    }

    public function transform(Data $data): array
    {
        return array_merge(
            $this->resolvePayload($data),
            $data->getAdditionalData()
        );
    }

    protected function resolvePayload(Data $data): array
    {
        $trees = $data->getInclusionTrees();

        $allowedIncludes = $this->transformationType->limitIncludesAndExcludes()
            ? $data->allowedRequestIncludes()
            : null;

        $allowedExcludes = $this->transformationType->limitIncludesAndExcludes()
            ? $data->allowedRequestExcludes()
            : null;

        $dataClass = $this->config->getDataClass($data::class);

        return $dataClass
            ->properties
            ->reduce(function (array $payload, DataProperty $property) use ($dataClass, $allowedExcludes, $allowedIncludes, $data, $trees) {
                $name = $property->name;

                if (! $this->shouldIncludeProperty($name, $data->{$name}, $trees, $allowedIncludes, $allowedExcludes)) {
                    return $payload;
                }

                $value = $this->resolvePropertyValue(
                    $property,
                    $data->{$name},
                    $trees->getNested($name),
                );

                if ($value instanceof Undefined) {
                    return $payload;
                }

                $mapper = $property->outputNameMapper ?? $dataClass->outputNameMapper;

                if ($mapper) {
                    $name = $mapper->inverse()->map($name);
                }

                $payload[$name] = $value;

                return $payload;
            }, []);
    }

    protected function shouldIncludeProperty(
        string $name,
        mixed $value,
        InclusionTrees $trees,
        ?array $allowedIncludes,
        ?array $allowedExcludes,
    ): bool {
        if ($value instanceof Undefined) {
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

        if ($this->isPropertyLazyExcluded($name, $trees, $allowedExcludes)) {
            return false;
        }

        return $this->isPropertyLazyIncluded($name, $value, $trees, $allowedIncludes);
    }

    protected function isPropertyHidden(
        string $name,
        InclusionTrees $trees,
    ): bool {
        if ($trees->except === ['*']) {
            return true;
        }

        if (array_key_exists($name, $trees->except ?? []) && empty($trees->except[$name])) {
            return true;
        }

        if ($trees->except !== null) {
            return false;
        }

        if ($trees->only === ['*']) {
            return false;
        }

        if (array_key_exists($name, $trees->only ?? [])) {
            return false;
        }

        if ($trees->only !== null) {
            return true;
        }

        return false;
    }

    protected function isPropertyLazyExcluded(
        string $name,
        InclusionTrees $trees,
        ?array $allowedExcludes,
    ): bool {
        if ($allowedExcludes !== null && ! in_array($name, $allowedExcludes)) {
            return false;
        }

        if ($trees->lazyExcluded === ['*']) {
            return true;
        }

        return array_key_exists($name, $trees->lazyExcluded ?? []);
    }

    protected function isPropertyLazyIncluded(
        string $name,
        Lazy $value,
        InclusionTrees $trees,
        ?array $allowedIncludes,
    ): bool {
        if ($allowedIncludes !== null && ! in_array($name, $allowedIncludes)) {
            return false;
        }

        if ($trees->lazyIncluded === ['*']) {
            return true;
        }

        if ($value->isDefaultIncluded()) {
            return true;
        }

        return array_key_exists($name, $trees->lazyIncluded ?? []);
    }

    protected function resolvePropertyValue(
        DataProperty $property,
        mixed $value,
        InclusionTrees $nestedPropertyTrees
    ): mixed {
        if ($value instanceof Lazy) {
            $value = $value->resolve();
        }

        if ($value === null) {
            return null;
        }

        if ($transformer = $this->resolveTransformerForValue($property, $value)) {
            return $transformer->transform($property, $value);
        }

        if ($value instanceof Data || $value instanceof DataCollection) {
            $value->withInclusionTrees($nestedPropertyTrees);

            return $this->transformationType->useTransformers()
                ? $value->transform($this->transformationType)
                : $value;
        }

        return $value;
    }

    private function resolveTransformerForValue(
        DataProperty $property,
        mixed $value,
    ): ?Transformer {
        if (! $this->transformationType->useTransformers()) {
            return null;
        }

        $transformer = $property->transformer ?? $this->config->findGlobalTransformerForValue($value);

        $shouldUseDefaultDataTransformer = $transformer instanceof ArrayableTransformer
            && ($property->isDataObject || $property->isDataCollection);

        if ($shouldUseDefaultDataTransformer) {
            return null;
        }

        return $transformer;
    }
}
