<?php

namespace Spatie\LaravelData\Transformers;

use ReflectionClass;
use ReflectionProperty;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\DataTransformers;

class DataTransformer implements Transformer
{
    private bool $withValueTransforming = true;

    public static function create(): self
    {
        return new self();
    }

    public function withoutValueTransforming(): static
    {
        $this->withValueTransforming = false;

        return $this;
    }

    public function canTransform(mixed $value): bool
    {
        return $value instanceof Data;
    }

    public function transform(mixed $value): mixed
    {
        /** @var \Spatie\LaravelData\Data $value */

        $payload = $this->resolvePayload($value, app(DataTransformers::class));

        $appended = $value->append();

        if (count($appended) > 0) {
            $payload = array_merge($payload, $appended);
        }

        return $payload;
    }

    private function resolvePayload(
        Data $data,
        DataTransformers $transformers,
    ): array {
        $reflection = new ReflectionClass($data);

        $inclusionTree = $data->getInclusionTree();
        $exclusionTree = $data->getExclusionTree();

        return array_reduce(
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            function (array $payload, ReflectionProperty $property) use ($data, $exclusionTree, $transformers, $inclusionTree) {
                $name = $property->getName();
                $value = $data->{$name};

                if ($this->shouldIncludeProperty($name, $value, $inclusionTree, $exclusionTree)) {
                    if ($value instanceof Lazy) {
                        $value = $value->resolve();
                    }

                    if ($value instanceof Data || $value instanceof DataCollection || $value instanceof PaginatedDataCollection) {
                        $payload[$name] = $value->withPartialsTrees(
                            $inclusionTree[$name] ?? [],
                            $exclusionTree[$name] ?? []
                        )->toArray();
                    } else {
                        $payload[$name] = $transformers->forValue($value)?->transform($value) ?? $value;
                    }
                }

                return $payload;
            },
            []
        );
    }

    private function shouldIncludeProperty(
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
}
