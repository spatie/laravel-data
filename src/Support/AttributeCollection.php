<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use ReflectionAttribute;

class AttributeCollection extends Collection
{
    private array $groups;

    public static function makeFromReflectionAttributes(array $attributes): self
    {
        return new self(
            array_map(
                fn (ReflectionAttribute $attribute) => $attribute->newInstance(),
                array_filter($attributes, fn (ReflectionAttribute $attribute) => class_exists($attribute->getName()))
            )
        );
    }

    public function add($item): static
    {
        unset($this->groups);

        return parent::add($item);
    }

    public function offsetSet($key, $value): void
    {
        unset($this->groups);
        parent::offsetSet($key, $value);
    }

    private function maybeProcessItemsIntoGroups(): void
    {
        if (! isset($this->groups)) {
            foreach ($this->items as $item) {
                $implements = class_implements($item);
                $parents = class_parents($item);
                foreach (array_merge([get_class($item)], $implements, $parents) as $parent) {
                    $this->groups[$parent][] = $item;
                }
            }
        }
    }

    /**
     * @param class-string $attributeClass
     */
    public function hasAttribute(string $attributeClass): bool
    {
        $this->maybeProcessItemsIntoGroups();

        return ! empty($this->groups[$attributeClass]);
    }

    /**
     * @template T of object
     * @param class-string<T> $attributeClass
     *
     * @return Collection<T>
     */
    public function getAttributes(string $attributeClass): Collection
    {
        $this->maybeProcessItemsIntoGroups();

        return collect($this->groups[$attributeClass] ?? []);
    }

    /**
     * @template T of object
     * @param class-string<T> $attributeClass
     *
     * @return ?T
     */
    public function getAttribute(string $attributeClass): ?object
    {
        $this->maybeProcessItemsIntoGroups();

        return current($this->groups[$attributeClass] ?? []) ?: null;
    }
}
