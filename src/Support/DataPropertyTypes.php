<?php

namespace Spatie\LaravelData\Support;

class DataPropertyTypes
{
    public function __construct(private array $types = [])
    {
    }

    public function add(string $type): void
    {
        $this->types[] = $type;
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function count(): int
    {
        return count($this->types);
    }

    public function all(): array
    {
        return $this->types;
    }

    public function first(): string
    {
        return current($this->types);
    }

    public function getImplementedType(string $type): ?string
    {
        foreach ($this->types as $propertyType) {
            if ($type === $propertyType) {
                return $propertyType;
            }

            if (is_a($propertyType, $type, true)) {
                return $propertyType;
            }
        }

        return null;
    }

    public function canBe(string $type): bool
    {
        return $this->getImplementedType($type) !== null;
    }
}
