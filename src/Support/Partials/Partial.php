<?php

namespace Spatie\LaravelData\Support\Partials;

use Closure;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Support\Partials\Segments\AllPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\FieldsPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\NestedPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\PartialSegment;
use Stringable;

class Partial implements Stringable
{
    protected readonly ResolvedPartial $resolvedPartial;

    /**
     * @param array<int, PartialSegment> $segments
     */
    public function __construct(
        public array $segments,
        public bool $permanent,
        public ?Closure $condition,
    ) {
        $this->resolvedPartial = new ResolvedPartial($segments);
    }

    public static function create(
        string $path,
        bool $permanent = false
    ): self {
        return new self(
            segments: self::resolveSegmentsFromPath($path),
            permanent: $permanent,
            condition: null,
        );
    }

    public static function createConditional(
        string $path,
        Closure $condition,
        bool $permanent = false
    ): self {
        return new self(
            segments: self::resolveSegmentsFromPath($path),
            permanent: $permanent,
            condition: $condition,
        );
    }

    public static function fromMethodDefinedKeyAndValue(
        string|int $key,
        string|bool|callable $value,
    ) {
        if (is_string($value)) {
            return self::create($value, permanent: true);
        }

        if (is_callable($value)) {
            return self::createConditional($key, $value, permanent: true);
        }

        return self::create($key, permanent: $value);
    }

    protected static function resolveSegmentsFromPath(string $path): array
    {
        $segmentStrings = explode('.', $path);
        $segmentsCount = count($segmentStrings);

        $segments = [];

        foreach ($segmentStrings as $index => $segmentString) {
            if ($segmentString === '*') {
                $segments[] = new AllPartialSegment();

                return $segments;
            }

            if (str_starts_with($segmentString, '{') && str_ends_with($segmentString, '}')) {
                $fields = explode(
                    ',',
                    substr($segmentString, 1, -1)
                );

                $segments[] = new FieldsPartialSegment(array_map(fn (string $field) => trim($field), $fields));

                return $segments;
            }

            if ($index !== $segmentsCount - 1) {
                $segments[] = new NestedPartialSegment($segmentString);

                continue;
            }

            if (empty($segmentString)) {
                continue;
            }

            $segments[] = new FieldsPartialSegment([$segmentString]);

            return $segments;
        }

        return $segments;
    }

    public function resolve(BaseData|BaseDataCollectable $data): ?ResolvedPartial
    {
        if ($this->condition === null) {
            return $this->resolvedPartial->reset();
        }

        if (($this->condition)($data)) {
            return $this->resolvedPartial->reset();
        }

        return null;
    }

    public function __toString(): string
    {
        return implode('.', $this->segments);
    }
}
