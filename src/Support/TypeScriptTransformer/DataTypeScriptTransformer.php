<?php

namespace Spatie\LaravelData\Support\TypeScriptTransformer;

use ReflectionClass;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;

class DataTypeScriptTransformer extends ClassTransformer
{
    protected function shouldTransform(ReflectionClass $reflection): bool
    {
        return $reflection->implementsInterface(BaseData::class);
    }

    protected function classPropertyProcessors(): array
    {
        return array_merge(parent::classPropertyProcessors(), [
            new DataUtilitiesClassPropertyProcessor(),
        ]);
    }
}
