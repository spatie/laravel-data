<?php

namespace Spatie\LaravelData\Support\TypeScriptTransformer;

use Exception;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\LaravelData\Undefined;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\TypeProcessors\TypeProcessor;

class RemoveUndefinedTypeProcessor implements TypeProcessor
{
    public function process(
        Type $type,
        ReflectionParameter | ReflectionMethod | ReflectionProperty $reflection,
        MissingSymbolsCollection $missingSymbolsCollection
    ): ?Type {
        if (! $type instanceof Compound) {
            return $type;
        }

        /** @var \Illuminate\Support\Collection $types */
        $types = collect(iterator_to_array($type->getIterator()))
            ->reject(function (Type $type) {
                if (! $type instanceof Object_) {
                    return false;
                }

                return is_a((string)$type->getFqsen(), Undefined::class, true);
            });

        if ($types->isEmpty()) {
            throw new Exception("Type {$reflection->getDeclaringClass()->name}:{$reflection->getName()} cannot be only Undefined");
        }

        if ($types->count() === 1) {
            return $types->first();
        }

        return new Compound($types->all());
    }
}
