<?php

namespace Spatie\LaravelData\Support\Factories;

use ReflectionClass;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\GetsCast;
use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Attributes\PropertyForMorph;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Mappers\NameMapper;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Resolvers\NameMappersResolver;
use Spatie\LaravelData\Support\Annotations\DataIterableAnnotation;
use Spatie\LaravelData\Support\DataProperty;

class DataPropertyFactory
{
    public function __construct(
        protected DataTypeFactory $typeFactory,
    ) {
    }

    public function build(
        ReflectionProperty $reflectionProperty,
        ReflectionClass $reflectionClass,
        bool $hasDefaultValue = false,
        mixed $defaultValue = null,
        ?NameMapper $classInputNameMapper = null,
        ?NameMapper $classOutputNameMapper = null,
        ?DataIterableAnnotation $classDefinedDataIterableAnnotation = null,
        ?AutoLazy $classAutoLazy = null,
    ): DataProperty {
        $attributes = DataAttributesCollectionFactory::buildFromReflectionProperty($reflectionProperty);

        $type = $this->typeFactory->buildProperty(
            $reflectionProperty->getType(),
            $reflectionClass,
            $reflectionProperty,
            $attributes,
            $classDefinedDataIterableAnnotation
        );

        $mappers = NameMappersResolver::create()->execute($attributes);

        $inputMappedName = match (true) {
            $mappers['inputNameMapper'] !== null => $mappers['inputNameMapper']->map($reflectionProperty->name),
            $classInputNameMapper !== null => $classInputNameMapper->map($reflectionProperty->name),
            default => null,
        };

        $outputMappedName = match (true) {
            $mappers['outputNameMapper'] !== null => $mappers['outputNameMapper']->map($reflectionProperty->name),
            $classOutputNameMapper !== null => $classOutputNameMapper->map($reflectionProperty->name),
            default => null,
        };

        if (! $reflectionProperty->isPromoted()) {
            $hasDefaultValue = $reflectionProperty->hasDefaultValue();
            $defaultValue = $reflectionProperty->getDefaultValue();
        }

        if ($hasDefaultValue && $defaultValue instanceof Optional) {
            $hasDefaultValue = false;
            $defaultValue = null;
        }

        $autoLazy = $attributes->first(AutoLazy::class);

        if ($classAutoLazy && $type->lazyType !== null && $autoLazy === null) {
            $autoLazy = $classAutoLazy;
        }

        $computed = $attributes->has(Computed::class);

        return new DataProperty(
            name: $reflectionProperty->name,
            className: $reflectionProperty->class,
            type: $type,
            validate: ! $computed && ! $attributes->has(WithoutValidation::class),
            computed: $computed,
            hidden: $attributes->has(Hidden::class),
            isPromoted: $reflectionProperty->isPromoted(),
            isReadonly: $reflectionProperty->isReadOnly(),
            morphable: $attributes->has(PropertyForMorph::class),
            autoLazy: $autoLazy,
            hasDefaultValue: $hasDefaultValue,
            defaultValue: $defaultValue,
            cast: $attributes->first(GetsCast::class)?->get(),
            transformer: ($attributes->first(WithTransformer::class) ?? $attributes->first(WithCastAndTransformer::class))?->get(),
            inputMappedName: $inputMappedName,
            outputMappedName: $outputMappedName,
            attributes: $attributes,
        );
    }
}
