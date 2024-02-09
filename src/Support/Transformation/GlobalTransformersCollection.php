<?php

namespace Spatie\LaravelData\Support\Transformation;

use ArrayIterator;
use IteratorAggregate;
use Spatie\LaravelData\Transformers\Transformer;
use Traversable;

class GlobalTransformersCollection implements IteratorAggregate
{
    /**
     * @param array<string, Transformer> $transformers
     */
    public function __construct(
        protected array $transformers = []
    ) {
    }

    public function add(string $transformable, Transformer $transformer): self
    {
        $this->transformers[ltrim($transformable, ' \\')] = $transformer;

        return $this;
    }

    public function findTransformerForValue(mixed $value): ?Transformer
    {
        if (gettype($value) !== 'object') {
            return $this->transformers[get_debug_type($value)] ?? null;
        }

        foreach ($this->transformers as $transformable => $transformer) {
            if ($value::class === $transformable) {
                return $transformer;
            }

            if (is_a($value::class, $transformable, true)) {
                return $transformer;
            }
        }

        return null;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->transformers);
    }
}
