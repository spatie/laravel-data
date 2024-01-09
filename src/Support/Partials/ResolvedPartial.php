<?php

namespace Spatie\LaravelData\Support\Partials;

use Spatie\LaravelData\Support\Partials\Segments\AllPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\FieldsPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\NestedPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\PartialSegment;
use Stringable;

class ResolvedPartial implements Stringable
{
    protected int $segmentCount;

    protected bool $endsInAll;

    /**
     * @param array<PartialSegment> $segments
     * @param int $pointer
     */
    public function __construct(
        public array $segments,
        public int $pointer = 0,
    ) {
        $this->segmentCount = count($segments);
        $this->endsInAll = $this->segmentCount === 0
            ? false
            : $this->segments[$this->segmentCount - 1] instanceof AllPartialSegment;
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

    public function __toString(): string
    {
        return implode('.', $this->segments)." (current: {$this->pointer})";
    }
}
