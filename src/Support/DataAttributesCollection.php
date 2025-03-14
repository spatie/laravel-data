<?php

namespace Spatie\LaravelData\Support;

use Attribute;

class DataAttributesCollection
{
    /**
     * @param array<string, array<int, object&Attribute>> $attributes
     */
    public function __construct(
        protected array $attributes = []
    ) {
    }

    public function has(string $type): bool
    {
        return array_key_exists($type, $this->attributes);
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     *
     * @return ?T
     */
    public function first(string $type): ?object
    {
        return $this->attributes[$type][0] ?? null;
    }

    /**
     * @template T of object
     * @param class-string<T> $type
     *
     * @return array<T>
     */
    public function all(string $type): array
    {
        return $this->attributes[$type] ?? [];
    }
}
