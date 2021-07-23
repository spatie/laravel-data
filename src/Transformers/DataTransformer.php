<?php

namespace Spatie\LaravelData\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataConfig;

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
        $reflection = new ReflectionClass($data);

        $inclusionTree = $data->getInclusionTree();
        $exclusionTree = $data->getExclusionTree();

        return array_reduce(
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            function (array $payload, ReflectionProperty $property) use ($data, $exclusionTree, $inclusionTree) {
                $name = $property->getName();

                if ($this->shouldIncludeProperty($name, $data->{$name}, $inclusionTree, $exclusionTree)) {
                    $payload[$name] = $this->resolvePropertyValue(
                        $data->{$name},
                        $inclusionTree[$name] ?? [],
                        $exclusionTree[$name] ?? []
                    );
                }

                return $payload;
            },
            []
        );
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
        mixed $value,
        array $nestedInclusionTree,
        array $nestedExclusionTree,
    ): mixed {
        if ($value instanceof Lazy) {
            $value = $value->resolve();
        }

        if ($value instanceof Data || $value instanceof DataCollection) {
            $value->withPartialsTrees($nestedInclusionTree, $nestedExclusionTree);

            return $this->withValueTransforming
                ? $value->toArray()
                : $value;
        }

        return $this->withValueTransforming
            ? $this->config->transform($value)
            : $value;
    }
}
