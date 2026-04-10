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

    public function containsWildcards(): bool
    {
        return in_array('*', $this->path, true);
    }

    /**
     * @param array $fullPayload
     * @return array<array-key,self>
     */
    public function matchingWildcardPayloadValidationPaths(array $fullPayload): array
    {
        return $this->expandWildcardPath($this->path, $fullPayload);
    }

    protected function expandWildcardPath(array $remainingSegments, mixed $payload, array $resolvedSegments = []): array
    {
        if (empty($remainingSegments)) {
            return [new self($resolvedSegments)];
        }

        if (! is_array($payload)) {
            return [];
        }

        $segment = array_shift($remainingSegments);

        if ($segment === '*') {
            $results = [];
            foreach (array_keys($payload) as $key) {
                array_push($results, ...$this->expandWildcardPath($remainingSegments, $payload[$key], array_merge($resolvedSegments, [$key])));
            }

            return $results;
        }

        if (array_key_exists($segment, $payload)) {
            return $this->expandWildcardPath($remainingSegments, $payload[$segment], array_merge($resolvedSegments, [$segment]));
        }

        return [];
    }
}
