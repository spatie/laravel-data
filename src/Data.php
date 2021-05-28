<?php

namespace Spatie\LaravelData;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

/**
 * @method static array create()
 */
abstract class Data implements Arrayable, Responsable
{
    use ResponsableData;

    private array $includes = [];

    public static function collection(Collection | array | LengthAwarePaginator $items): DataCollection | PaginatedDataCollection
    {
        if ($items instanceof LengthAwarePaginator) {
            return new PaginatedDataCollection(static::class, $items);
        }

        return new DataCollection(static::class, $items);
    }

    public function endpoints(): array
    {
        return [];
    }

    public function include(string ...$includes): static
    {
        $this->includes = array_unique(array_merge($this->includes, $includes));

        return $this;
    }

    public function all(): array
    {
        $reflection = new ReflectionClass($this);

        $includes = $this->getIncludesForResource();
        $endpoints = $this->endpoints();

        $payload = [];

        if (count($endpoints) > 0) {
            $payload['endpoints'] = $endpoints;
        }

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            $value = $this->{$name};

            if ($this->shouldIncludeProperty($name, $value, $includes)) {
                $payload[$name] = $value;
            }
        }

        return $payload;
    }

    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);

        $includes = $this->getIncludesForResource();

        /** @var \Spatie\LaravelData\DataTransformers $transformers */
        $transformers = app(DataTransformers::class);

        $payload = array_reduce(
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            function (array $payload, ReflectionProperty $property) use ($transformers, $includes) {
                $name = $property->getName();
                $value = $this->{$name};

                if ($this->shouldIncludeProperty($name, $value, $includes)) {
                    if ($value instanceof Lazy) {
                        $value = $value->resolve();
                    }

                    $payload[$name] = $transformers->forValue($value)?->transform(
                        $value,
                        $includes[$name] ?? []
                    ) ?? $value;
                }

                return $payload;
            },
            []
        );

        $endpoints = $this->endpoints();

        if (count($endpoints) > 0) {
            $payload['endpoints'] = $endpoints;
        }

        return $payload;
    }

    public static function empty(array $extra = []): array
    {
        $reflection = new ReflectionClass(static::class);

        $defaultConstructorProperties = $reflection->hasMethod('__construct')
            ? collect($reflection->getMethod('__construct')->getParameters())
                ->filter(fn (ReflectionParameter $parameter) => $parameter->isPromoted() && $parameter->isDefaultValueAvailable())
                ->mapWithKeys(fn (ReflectionParameter $parameter) => [
                    $parameter->name => $parameter->getDefaultValue(),
                ])
                ->toArray()
            : [];

        $defaults = array_merge(
            $reflection->getDefaultProperties(),
            $defaultConstructorProperties
        );

        return array_reduce(
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            function (array $payload, ReflectionProperty $property) use ($defaults, $extra) {
                $name = $property->getName();

                if (array_key_exists($name, $extra)) {
                    $payload[$name] = $extra[$name];

                    return $payload;
                }

                if (array_key_exists($name, $defaults)) {
                    $payload[$name] = $defaults[$name];

                    return $payload;
                }

                $propertyHelper = new DataPropertyHelper($property);

                $payload[$name] = $propertyHelper->getEmptyValue();

                return $payload;
            },
            []
        );
    }

    private function shouldIncludeProperty(string $name, $value, array $includes): bool
    {
        if (! $value instanceof Lazy) {
            return true;
        }

        return array_key_exists($name, $includes);
    }

    private function getPropertyValue(string $name, $value, array $includes): mixed
    {
        if ($value instanceof Lazy) {
            $value = $value->resolve();
        }


        return $value;
    }

    private function getIncludesForResource(): array
    {
        return array_reduce($this->includes, function (array $includes, $include) {
            if (! Str::contains($include, '.') && array_key_exists($include, $includes) === false) {
                $includes[$include] = [];

                return $includes;
            }

            $property = Str::before($include, '.');
            $otherIncludes = Str::after($include, '.');

            if (array_key_exists($property, $includes)) {
                $includes[$property][] = $otherIncludes;
            } else {
                $includes[$property] = [$otherIncludes];
            }

            return $includes;
        }, []);
    }
}
