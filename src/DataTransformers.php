<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\Transformers\DataTransformer;
use Spatie\LaravelData\Transformers\Transformer;

class DataTransformers
{
    /** @var \Spatie\LaravelData\Transformers\Transformer[] */
    protected array $transformers = [];

    public function __construct(array $userTransformers)
    {
        $this->transformers = array_map(
            function (string $transformer) {
                return app($transformer);
            },
            array_merge($this->defaultTransformers(), $userTransformers)
        );
    }

    public function forValue(mixed $value): ?Transformer
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->canTransform($value)) {
                return $transformer;
            }
        }

        return null;
    }

    public function get(): array
    {
        return $this->transformers;
    }

    protected function defaultTransformers(): array
    {
        return [
            DataTransformer::class,
            DataCollection::class,
        ];
    }
}
