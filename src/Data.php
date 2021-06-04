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
    use ResponsableData, IncludeableData;

    public static function collection(Collection|array|LengthAwarePaginator $items): DataCollection|PaginatedDataCollection
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

    public function all(): array
    {
        // TODO: reimplement this obv `toArray`
    }

    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);

        $includes = $this->getDirectivesForResource($this->includes);
        $excludes = $this->getDirectivesForResource($this->excludes);

        /** @var \Spatie\LaravelData\DataTransformers $transformers */
        $transformers = app(DataTransformers::class);

        $payload = array_reduce(
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            function (array $payload, ReflectionProperty $property) use ($excludes, $transformers, $includes) {
                $name = $property->getName();
                $value = $this->{$name};

                if ($this->shouldIncludeProperty($name, $value, $includes, $excludes)) {
                    if ($value instanceof Lazy) {
                        $value = $value->resolve();
                    }

                    $payload[$name] = $transformers->forValue($value)?->transform(
                            $value,
                            $includes[$name] ?? [],
                            $excludes[$name] ?? [],
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
                ->filter(fn(ReflectionParameter $parameter) => $parameter->isPromoted() && $parameter->isDefaultValueAvailable())
                ->mapWithKeys(fn(ReflectionParameter $parameter) => [
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

    private function shouldIncludeProperty(
        string $name,
        $value,
        array $includes,
        array $excludes
    ): bool {
        if (! $value instanceof Lazy) {
            return true;
        }

        if (array_key_exists($name, $excludes)) {
            return false;
        }

        if ($value->shouldInclude() ) {
            return true;
        }

        return array_key_exists($name, $includes);
    }

    private function getDirectivesForResource(array $directives): array
    {
        return array_reduce($directives, function (array $directives, $directive) {
            if (! Str::contains($directive, '.') && array_key_exists($directive, $directives) === false) {
                $directives[$directive] = [];

                return $directives;
            }

            $property = Str::before($directive, '.');
            $otherDirectives = Str::after($directive, '.');

            if (array_key_exists($property, $directives)) {
                $directives[$property][] = $otherDirectives;
            } else {
                $directives[$property] = [$otherDirectives];
            }

            return $directives;
        }, []);
    }
}
