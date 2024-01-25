<?php

namespace Spatie\LaravelData\Support\Validation;

use Stringable;

class ValidationPath implements Stringable
{
    public function __construct(
        protected readonly array $path = []
    ) {
    }

    public static function create(?string $path = null): self
    {
        if ($path === null) {
            return new self();
        }

        return new self(explode('.', $path));
    }

    public function property(string $property): self
    {
        $newPath = $this->path;

        $newPath[] = $property;

        return new self($newPath);
    }

    public function isRoot(): bool
    {
        return empty($this->path);
    }

    public function equals(string|ValidationPath $other): bool
    {
        $otherPath = $other instanceof ValidationPath
            ? $other->path
            : explode('.', $other);

        return $this->path === $otherPath;
    }

    public function get(): ?string
    {
        return implode('.', $this->path);
    }

    public function __toString()
    {
        return $this->get();
    }
}
