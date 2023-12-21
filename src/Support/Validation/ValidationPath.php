<?php

namespace Spatie\LaravelData\Support\Validation;

use Stringable;

class ValidationPath implements Stringable
{
    public function __construct(
        protected readonly ?string $path
    ) {
    }

    public static function create(?string $path = null): self
    {
        return new self($path);
    }

    public function property(string $property): self
    {
        return new self($this->path ? "{$this->path}.{$property}" : $property);
    }

    public function isRoot(): bool
    {
        return $this->path === null;
    }

    public function equals(string|ValidationPath $other): bool
    {
        $otherPath = $other instanceof ValidationPath ? $other->path : $other;

        return $this->path === $otherPath;
    }

    public function get(): ?string
    {
        return $this->path;
    }

    public function __toString()
    {
        return $this->get();
    }
}
