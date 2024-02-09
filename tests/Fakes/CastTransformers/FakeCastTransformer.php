<?php

namespace Spatie\LaravelData\Tests\Fakes\CastTransformers;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

class FakeCastTransformer implements Cast, Transformer
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        return $value;
    }

    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        return $value;
    }
}
