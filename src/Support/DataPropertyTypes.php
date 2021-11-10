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

    public function canBe(string $type): bool
    {
        foreach ($this->types as $propertyType) {
            if ($type === $propertyType) {
                return true;
            }

            if (is_a($propertyType, $type, true)) {
                return true;
            }
        }

        return false;
    }
}
