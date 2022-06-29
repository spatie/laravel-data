<?php

namespace Spatie\LaravelData\Support\TypeScriptTransformer;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\TypeProcessors\TypeProcessor;

class DataCollectionTypeProcessor implements TypeProcessor
{
    public function process(
        Type $type,
        ReflectionParameter|ReflectionMethod|ReflectionProperty $reflection,
        MissingSymbolsCollection $missingSymbolsCollection
    ): ?Type {
        $attributes = $reflection->getAttributes(DataCollectionOf::class);

        if (count($attributes) === 0) {
            return $type;
        }

        $class = $attributes[0]->getArguments()[0];

        if (! class_exists($class)) {
            return $type;
        }

        $baseType = new Array_(new Object_(new Fqsen("\\{$class}")));

        if ($type instanceof Nullable) {
            return new Nullable($baseType);
        }

        if ($type instanceof Compound && $type->contains(new Null_())) {
            return new Compound([new Null_(), $baseType]);
        }

        return $baseType;
    }
}
