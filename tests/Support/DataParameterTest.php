<?php

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\DataParameter;
use Spatie\LaravelData\Support\Types\Type;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('can create a data parameter', function () {
    $class = new class ('', '', '', CreationContextFactory::createFromConfig(SimpleData::class)->get()) extends Data {
        public function __construct(
            string $nonPromoted,
            public $withoutType,
            public string $property,
            CreationContext $creationContext,
            public string $propertyWithDefault = 'hello',
        ) {
        }
    };

    $reflection = new ReflectionParameter([$class::class, '__construct'], 'nonPromoted');
    $parameter = DataParameter::create($reflection, $class::class);

    expect($parameter)
        ->name->toEqual('nonPromoted')
        ->isPromoted->toBeFalse()
        ->hasDefaultValue->toBeFalse()
        ->defaultValue->toBeNull()
        ->type->toEqual(Type::forReflection($reflection->getType(), $class::class))
        ->isCreationContext->toBeFalse();

    $reflection = new ReflectionParameter([$class::class, '__construct'], 'withoutType');
    $parameter = DataParameter::create($reflection, $class::class);

    expect($parameter)
        ->name->toEqual('withoutType')
        ->isPromoted->toBeTrue()
        ->hasDefaultValue->toBeFalse()
        ->defaultValue->toBeNull()
        ->type->toEqual(Type::forReflection($reflection->getType(), $class::class))
        ->isCreationContext->toBeFalse();

    $reflection = new ReflectionParameter([$class::class, '__construct'], 'property');
    $parameter = DataParameter::create($reflection, $class::class);

    expect($parameter)
        ->name->toEqual('property')
        ->isPromoted->toBeTrue()
        ->hasDefaultValue->toBeFalse()
        ->defaultValue->toBeNull()
        ->type->toEqual(Type::forReflection($reflection->getType(), $class::class))
        ->isCreationContext->toBeFalse();

    $reflection = new ReflectionParameter([$class::class, '__construct'], 'creationContext');
    $parameter = DataParameter::create($reflection, $class::class);

    expect($parameter)
        ->name->toEqual('creationContext')
        ->isPromoted->toBeFalse()
        ->hasDefaultValue->toBeFalse()
        ->defaultValue->toBeNull()
        ->type->toEqual(Type::forReflection($reflection->getType(), $class::class))
        ->isCreationContext->toBeTrue();

    $reflection = new ReflectionParameter([$class::class, '__construct'], 'propertyWithDefault');
    $parameter = DataParameter::create($reflection, $class::class);

    expect($parameter)
        ->name->toEqual('propertyWithDefault')
        ->isPromoted->toBeTrue()
        ->hasDefaultValue->toBeTrue()
        ->defaultValue->toEqual('hello')
        ->type->toEqual(Type::forReflection($reflection->getType(), $class::class))
        ->isCreationContext->toBeFalse();
});
