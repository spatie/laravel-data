<?php

namespace Spatie\LaravelData\Support\TypeScriptTransformer;

use Exception;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\LaravelData\Lazy;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\TypeProcessors\TypeProcessor;

class RemoveLazyTypeProcessor implements TypeProcessor
{
    public function process(Type $type, ReflectionParameter | ReflectionMethod | ReflectionProperty $reflection, MissingSymbolsCollection $missingSymbolsCollection): ?Type
    {
        if (! $type instanceof Compound) {
            return $type;
        }

        /** @var \Illuminate\Support\Collection $otherTypes */
        [, $otherTypes] = collect(iterator_to_array($type->getIterator()))
            ->partition(function (Type $type) {
                if (! $type instanceof Object_) {
                    return false;
                }

                return is_a((string)$type->getFqsen(), Lazy::class, true);
            });

        if ($otherTypes->isEmpty()) {
            throw new Exception("Type {$reflection->getDeclaringClass()->name}:{$reflection->getName()} cannot be only Lazy");
        }

        if ($otherTypes->count() === 1) {
            return $otherTypes->first();
        }

        return new Compound($otherTypes->all());
    }
}
