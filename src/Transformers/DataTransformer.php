<?php

namespace Spatie\LaravelData\Transformers;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class DataTransformer
{
    private bool $withValueTransforming = true;

    private DataConfig $config;

    public static function create(): self
    {
        return new self();
    }

    public function __construct()
    {
        $this->config = app(DataConfig::class);
    }

    public function withoutValueTransforming(): static
    {
        $this->withValueTransforming = false;

        return $this;
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

        return $this->config
            ->getDataClass($data::class)
            ->properties()
            ->reduce(function (array $payload, DataProperty $property) use ($data, $exclusionTree, $inclusionTree) {
                $name = $property->name();

                if ($this->shouldIncludeProperty($name, $data->{$name}, $inclusionTree, $exclusionTree)) {
                    $payload[$name] = $this->resolvePropertyValue(
                        $property,
                        $data->{$name},
                        $inclusionTree[$name] ?? [],
                        $exclusionTree[$name] ?? []
                    );
                }

                return $payload;
            }, []);
    }

    protected function shouldIncludeProperty(
        string $name,
        mixed $value,
        array $includes,
        array $excludes
    ): bool {
        if (! $value instanceof Lazy) {
            return true;
        }

        if ($excludes === ['*']) {
            return false;
        }

        if (array_key_exists($name, $excludes)) {
            return false;
        }

        if ($includes === ['*']) {
            return true;
        }

        if ($value->shouldInclude()) {
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
        if ($value === null) {
            return null;
        }

        if ($value instanceof Lazy) {
            $value = $value->resolve();
        }

        if ($value instanceof Data || $value instanceof DataCollection) {
            $value->withPartialsTrees($nestedInclusionTree, $nestedExclusionTree);

            return $this->withValueTransforming
                ? $value->toArray()
                : $value;
        }

        if (! $this->withValueTransforming) {
            return $value;
        }

        $transformer = $property->transformerAttribute()?->get() ?? $this->config->findTransformerForValue($value);

        return $transformer?->transform($value) ?? $value;
    }
}
