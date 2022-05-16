<?php

namespace Spatie\LaravelData\Support\TypeScriptTransformer;

use ReflectionClass;
use ReflectionProperty;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataConfig;
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
            new DataCollectionTypeProcessor(),
            new RemoveOptionalTypeProcessor(),
            new DtoCollectionTypeProcessor(),
        ];
    }


    protected function transformProperties(
        ReflectionClass $class,
        MissingSymbolsCollection $missingSymbols
    ): string {
        $dataClass = app(DataConfig::class)->getDataClass($class->getName());

        return array_reduce(
            $this->resolveProperties($class),
            function (string $carry, ReflectionProperty $property) use ($dataClass, $missingSymbols) {
                $type = $this->reflectionToType(
                    $property,
                    $missingSymbols,
                    ...$this->typeProcessors()
                );

                if ($type === null) {
                    return $carry;
                }

                /** @var \Spatie\LaravelData\Support\DataProperty $dataProperty */
                $dataProperty = $dataClass->properties[$property->getName()];

                $isOptional = ($this->config->shouldConsiderNullAsOptional() && $dataProperty->type->isNullable)
                    || $dataProperty->type->isLazy
                    || $dataProperty->type->isUndefinable;

                $transformed = $this->typeToTypeScript(
                    $type,
                    $missingSymbols,
                    $isOptional,
                    $property->getDeclaringClass()->getName(),
                );

                return $isOptional
                    ? "{$carry}{$property->getName()}?: {$transformed};" . PHP_EOL
                    : "{$carry}{$property->getName()}: {$transformed};" . PHP_EOL;
            },
            ''
        );
    }
}
