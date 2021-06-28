<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Transformers\Transformer;

class DataTransformers
{
    /** @var \Spatie\LaravelData\Transformers\Transformer[] */
    protected array $transformers = [];

    public function __construct(array $transformers)
    {
        $this->transformers = array_map(
            fn (string $transformer) => app($transformer),
            $transformers
        );
    }

    public function transform(mixed $value): mixed
    {
        $transformer = $this->findTransformerForValue($value);

        return $transformer?->transform($value) ?? $value;
    }

    protected function findTransformerForValue(mixed $value): ?Transformer
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->canTransform($value)) {
                return $transformer;
            }
        }

        return null;
    }
}
