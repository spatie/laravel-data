<?php

namespace Spatie\LaravelData\Support\TypeScriptTransformer;

use ReflectionClass;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\TypeScriptTransformer\Collectors\Collector;
use Spatie\TypeScriptTransformer\Structures\TransformedType;

class DataTypeScriptCollector extends Collector
{
    public function getTransformedType(ReflectionClass $class): ?TransformedType
    {
        if (! $class->isSubclassOf(BaseData::class)) {
            return null;
        }

        $transformer = new DataTypeScriptTransformer($this->config);

        return $transformer->transform($class, $class->getShortName());
    }
}
