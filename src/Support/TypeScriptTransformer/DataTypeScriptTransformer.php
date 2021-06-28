<?php

namespace Spatie\LaravelData\Support\TypeScriptTransformer;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelTypeScriptTransformer\Transformers\DtoTransformer;
use Spatie\TypeScriptTransformer\Structures\MissingSymbolsCollection;
use Spatie\TypeScriptTransformer\TypeProcessors\DtoCollectionTypeProcessor;
use Spatie\TypeScriptTransformer\TypeProcessors\ReplaceDefaultsTypeProcessor;

class DataTypeScriptTransformer extends DtoTransformer
{
    public function canTransform(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(Data::class);
    }

    protected function typeProcessors(): array
    {
        return [
            new ReplaceDefaultsTypeProcessor(
                $this->config->getDefaultTypeReplacements()
            ),
            new RemoveLazyTypeProcessor(),
            new DtoCollectionTypeProcessor(),
        ];
    }


    protected function transformProperties(
        ReflectionClass $class,
        MissingSymbolsCollection $missingSymbols
    ): string {
        return array_reduce(
            $this->resolveProperties($class),
            function (string $carry, ReflectionProperty $property) use ($missingSymbols) {
                $type = $this->reflectionToType(
                    $property,
                    $missingSymbols,
                    ...$this->typeProcessors()
                );

                if ($type === null) {
                    return $carry;
                }

                $transformed = $this->typeToTypeScript(
                    $type,
                    $missingSymbols,
                    $property->getDeclaringClass()->getName()
                );

                return $this->isPropertyLazy($property)
                    ? "{$carry}{$property->getName()}?: {$transformed};" . PHP_EOL
                    : "{$carry}{$property->getName()}: {$transformed};" . PHP_EOL;
            },
            ''
        );
    }

    private function isPropertyLazy(ReflectionProperty $property): bool
    {
        $type = $property->getType();

        if ($type === null || $type instanceof ReflectionNamedType) {
            return false;
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $childType) {
                if ($childType->getName() === Lazy::class) {
                    return true;
                }
            }
        }

        return false;
    }
}
