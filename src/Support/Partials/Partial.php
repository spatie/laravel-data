<?php

namespace Spatie\LaravelData\Support\Partials;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Support\Partials\Segments\AllPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\FieldsPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\NestedPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\PartialSegment;
use Stringable;

class Partial implements Stringable
{
    public int $segmentCount;

    public bool $endsInAll;

    /**
     * @param array<int, PartialSegment> $segments
     */
    public function __construct(
        public array $segments,
        public bool $permanent = false,
        public ?Closure $condition = null,
        public int $pointer = 0,
    ) {
        $this->segmentCount = count($segments);
        $this->endsInAll = $this->segmentCount === 0
            ? false
            : $this->segments[$this->segmentCount - 1] instanceof AllPartialSegment;
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

    public function isUndefined(): bool
    {
        return ! $this->endsInAll && $this->pointer >= $this->segmentCount;
    }

    public function isAll(): bool
    {
        return $this->endsInAll && $this->pointer >= $this->segmentCount - 1;
    }

    public function getNested(): ?string
    {
        $segment = $this->getCurrentSegment();

        if ($segment === null) {
            return null;
        }

        if (! $segment instanceof NestedPartialSegment) {
            return null;
        }

        return $segment->field;
    }

    public function getFields(): ?array
    {
        if ($this->isUndefined()) {
            return null;
        }

        $segment = $this->getCurrentSegment();

        if ($segment === null) {
            return null;
        }

        if (! $segment instanceof FieldsPartialSegment) {
            return null;
        }

        return $segment->fields;
    }

    /** @return string[] */
    public function toLaravel(): array
    {
        /** @var array<string> $segments */
        $segments = [];

        for ($i = $this->pointer; $i < $this->segmentCount; $i++) {
            $segment = $this->segments[$i];

            if ($segment instanceof AllPartialSegment) {
                $segments[] = '*';

                continue;
            }

            if ($segment instanceof NestedPartialSegment) {
                $segments[] = $segment->field;

                continue;
            }

            if ($segment instanceof FieldsPartialSegment) {
                $segmentsAsString = count($segments) === 0
                    ? ''
                    : implode('.', $segments).'.';

                return array_map(
                    fn (string $field) => "{$segmentsAsString}{$field}",
                    $segment->fields
                );
            }
        }

        return [implode('.', $segments)];
    }

    public function next(): self
    {
        $this->pointer++;

        return $this;
    }

    public function rollbackWhenRequired(): void
    {
        $this->pointer--;
    }

    public function reset(): self
    {
        $this->pointer = 0;

        return $this;
    }

    protected function getCurrentSegment(): ?PartialSegment
    {
        return $this->segments[$this->pointer] ?? null;
    }

    public function isRequired(BaseData|BaseDataCollectable $data): bool
    {
        if ($this->condition === null) {
            return true;
        }

        return ($this->condition)($data);
    }


    public function toArray(): array
    {
        return [
            'segments' => $this->segments,
            'permanent' => $this->permanent,
            'condition' => $this->condition,
        ];
    }

    public function toSerializedArray(): array
    {
        return [
            'segments' => $this->segments,
            'permanent' => $this->permanent,
            'condition' => $this->condition
                ? serialize(new SerializableClosure($this->condition))
                : null,
            'pointer' => $this->pointer,
        ];
    }

    public static function fromSerializedArray(array $partial): Partial
    {
        return new self(
            segments: $partial['segments'],
            permanent: $partial['permanent'],
            condition: $partial['condition']
                ? unserialize($partial['condition'])->getClosure()
                : null,
            pointer: $partial['pointer'],
        );
    }

    public function __toString(): string
    {
        return implode('.', $this->segments)." (current: {$this->pointer})";
    }

    public function __serialize(): array
    {
        return [
            'segmentCount' => $this->segmentCount,
            'endsInAll' => $this->endsInAll,
            'segments' => $this->segments,
            'condition' => $this->condition
                ? serialize(new SerializableClosure($this->condition))
                : null,
            'permanent' => $this->permanent,
            'pointer' => $this->pointer,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->segmentCount = $data['segmentCount'];
        $this->endsInAll = $data['endsInAll'];
        $this->segments = $data['segments'];
        $this->pointer = $data['pointer'];
        $this->condition = $data['condition']
            ? unserialize($data['condition'])->getClosure()
            : null;
        $this->permanent = $data['permanent'];
    }
}
