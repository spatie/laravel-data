<?php

namespace Spatie\LaravelData;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use ReflectionClass;
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

    public function append(): array
    {
        $endpoints = $this->endpoints();

        if (count($endpoints) === 0) {
            return [];
        }

        return [
            'endpoints' => $endpoints,
        ];
    }

    public function all(): array
    {
        // TODO: reimplement this obv `toArray`
    }

    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);

        $inclusionTree = $this->inclusionTree ?? (new PartialsParser())->execute($this->includes);
        $exclusionTree = $this->exclusionTree ?? (new PartialsParser())->execute($this->excludes);

        /** @var \Spatie\LaravelData\DataTransformers $transformers */
        $transformers = app(DataTransformers::class);

        $payload = array_reduce(
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            function (array $payload, ReflectionProperty $property) use ($exclusionTree, $transformers, $inclusionTree) {
                $name = $property->getName();
                $value = $this->{$name};

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

        $appended = $this->append();

        if (count($appended) > 0) {
            $payload = array_merge($payload, $appended);
        }

        return $payload;
    }

    public static function empty(array $extra = []): array
    {
        $reflection = new ReflectionClass(static::class);

        return EmptyDataResolver::create($reflection)->get($extra);
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
