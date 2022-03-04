<?php

namespace Spatie\LaravelData\Transformers;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
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
        $inclusionTree = $data->getInclusionTree();
        $exclusionTree = $data->getExclusionTree();

        $allowedIncludes = $this->transformationType->limitIncludesAndExcludes()
            ? $data->allowedRequestIncludes()
            : null;

        $allowedExcludes = $this->transformationType->limitIncludesAndExcludes()
            ? $data->allowedRequestExcludes()
            : null;

        return $this->config
            ->getDataClass($data::class)
            ->properties()
            ->reduce(function (array $payload, DataProperty $property) use ($allowedExcludes, $allowedIncludes, $data, $exclusionTree, $inclusionTree) {
                $name = $property->name;

                if (! $this->shouldIncludeProperty($name, $data->{$name}, $inclusionTree, $exclusionTree, $allowedIncludes, $allowedExcludes)) {
                    return $payload;
                }

                $value = $this->resolvePropertyValue(
                    $property,
                    $data->{$name},
                    $inclusionTree[$name] ?? [],
                    $exclusionTree[$name] ?? []
                );

                if ($value instanceof Undefined) {
                    return $payload;
                }

                $payload[$name] = $value;

                return $payload;
            }, []);
    }

    protected function shouldIncludeProperty(
        string $name,
        mixed $value,
        array $includes,
        array $excludes,
        ?array $allowedIncludes,
        ?array $allowedExcludes,
    ): bool {
        if ($value instanceof Undefined) {
            return false;
        }

        if (! $value instanceof Lazy) {
            return true;
        }

        if ($value->isConditional()) {
            return ($value->getCondition())();
        }

        if ($this->isPropertyExcluded($name, $excludes, $allowedExcludes)) {
            return false;
        }

        return $this->isPropertyIncluded($name, $value, $includes, $allowedIncludes);
    }

    protected function isPropertyExcluded(
        string $name,
        array $excludes,
        ?array $allowedExcludes,
    ): bool {
        if ($allowedExcludes !== null && ! in_array($name, $allowedExcludes)) {
            return false;
        }

        if ($excludes === ['*']) {
            return true;
        }

        if (array_key_exists($name, $excludes)) {
            return true;
        }

        return false;
    }

    protected function isPropertyIncluded(
        string $name,
        Lazy $value,
        array $includes,
        ?array $allowedIncludes,
    ): bool {
        if ($allowedIncludes !== null && ! in_array($name, $allowedIncludes)) {
            return false;
        }

        if ($includes === ['*']) {
            return true;
        }

        if ($value->isDefaultIncluded()) {
            return true;
        }

        return array_key_exists($name, $includes);
    }

    protected function resolvePropertyValue(
        DataProperty $property,
        mixed $value,
        array $nestedInclusionTree,
        array $nestedExclusionTree,
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
            $value->withPartialsTrees($nestedInclusionTree, $nestedExclusionTree);

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
