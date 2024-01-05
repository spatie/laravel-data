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

    /**
     * @param array<PartialSegment> $segments
     * @param int $pointer
     */
    public function __construct(
        public array $segments,
        public int $pointer = 0,
    ) {
        $this->segmentCount = count($segments);
    }

    public function isUndefined()
    {
        return $this->pointer === $this->segmentCount;
    }

    public function isAll()
    {
        return $this->getCurrentSegment() instanceof AllPartialSegment;
    }

    public function getNested(): ?string
    {
        $segment = $this->getCurrentSegment();

        if (! $segment instanceof NestedPartialSegment) {
            return null;
        }

        return $segment->field;
    }

    public function getFields(): ?array
    {
        $segment = $this->getCurrentSegment();

        if (! $segment instanceof FieldsPartialSegment) {
            return null;
        }

        return $segment->fields;
    }

    /** @return string[] */
    public function toLaravel(): array
    {
        /** @var array<string> $partials */
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
                    : implode('.', $segments) . '.';

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
        if ($this->isUndefined() || $this->isAll()) {
            return $this;
        }

        $this->pointer++;

        return $this;
    }

    public function back(): self
    {
        $this->pointer--;

        return $this;
    }

    public function reset(): self
    {
        $this->pointer = 0;

        return $this;
    }

    public function getCurrentSegment(): PartialSegment
    {
        return $this->segments[$this->pointer];
    }

    public function __toString(): string
    {
        return implode('.', $this->segments) . " (current: {$this->pointer})";
    }
}
