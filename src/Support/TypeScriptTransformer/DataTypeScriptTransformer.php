<?php

namespace Spatie\LaravelData\Support\TypeScriptTransformer;

use ReflectionClass;
use ReflectionProperty;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\TypeScriptTransformer\Laravel\ClassPropertyProcessors\RemoveDataLazyTypeClassPropertyProcessor;
use Spatie\TypeScriptTransformer\Transformers\ClassTransformer;
use Spatie\TypeScriptTransformer\TypeScript\TypeScriptNode;

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
