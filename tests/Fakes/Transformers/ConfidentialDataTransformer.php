<?php

namespace Spatie\LaravelData\Tests\Fakes\Transformers;

use function collect;

use Spatie\LaravelData\Support\DataProperty;

use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Transformers\Transformer;

class ConfidentialDataTransformer implements Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed
    {
        /** @var \Spatie\LaravelData\Data $value */
        return collect($value->toArray())->map(fn (mixed $value) => 'CONFIDENTIAL')->toArray();
    }
}
