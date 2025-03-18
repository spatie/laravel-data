<?php

namespace Spatie\LaravelData\Tests\Support;

use Attribute;
use ReflectionClass;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\AutoClosureLazy;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\GetsCast;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Support\Factories\DataAttributesCollectionFactory;
use Spatie\LaravelData\Tests\Fakes\Casts\ConfidentialDataCast;

#[MapInputName(CamelCaseMapper::class)]
class TestClassWithAttribute
{
}

#[MapOutputName(CamelCaseMapper::class)]
class TestInheritedClassWithAttribute extends TestClassWithAttribute
{
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class RepeatableAttribute
{
    public function __construct(public string $name)
    {
    }
}

it('can get the attributes from a reflection class', function () {
    $attributes = DataAttributesCollectionFactory::buildFromReflectionClass(
        new ReflectionClass(TestClassWithAttribute::class)
    );

    expect($attributes->has(MapInputName::class))->toBe(true);
    expect($attributes->first(MapInputName::class))->toBeInstanceOf(MapInputName::class);
});

it('can get attributes from a reflection class and its parents', function () {
    $attributes = DataAttributesCollectionFactory::buildFromReflectionClass(
        new ReflectionClass(TestInheritedClassWithAttribute::class)
    );

    expect($attributes->has(MapInputName::class))->toBe(true);
    expect($attributes->first(MapInputName::class))->toBeInstanceOf(MapInputName::class);

    expect($attributes->has(MapOutputName::class))->toBe(true);
    expect($attributes->first(MapOutputName::class))->toBeInstanceOf(MapOutputName::class);
});

it('can get attributes for a reflection property', function () {
    $class = new class () {
        #[MapInputName(CamelCaseMapper::class)]
        protected string $first_name;
    };

    $attributes = DataAttributesCollectionFactory::buildFromReflectionProperty(
        new ReflectionProperty($class, 'first_name')
    );

    expect($attributes->has(MapInputName::class))->toBe(true);
    expect($attributes->first(MapInputName::class))->toBeInstanceOf(MapInputName::class);
});

it('can get multiple versions of an attribute attributes for a reflection property', function () {
    $class = new class () {
        #[RepeatableAttribute('a')]
        #[RepeatableAttribute('b')]
        protected string $first_name;
    };

    $attributes = DataAttributesCollectionFactory::buildFromReflectionProperty(
        new ReflectionProperty($class, 'first_name')
    );

    expect($attributes->has(RepeatableAttribute::class))->toBe(true);

    expect($attributes->all(RepeatableAttribute::class)[0])
        ->toBeInstanceOf(RepeatableAttribute::class)
        ->name->toBe('a');

    expect($attributes->all(RepeatableAttribute::class)[1])
        ->toBeInstanceOf(RepeatableAttribute::class)
        ->name->toBe('b');
});

it('can get the attribute by its parent class', function () {
    $class = new class () {
        #[AutoClosureLazy()]
        protected string $first_name;
    };

    $attributes = DataAttributesCollectionFactory::buildFromReflectionProperty(
        new ReflectionProperty($class, 'first_name')
    );

    expect($attributes->has(AutoLazy::class))->toBe(true);
    expect($attributes->first(AutoLazy::class))->toBeInstanceOf(AutoClosureLazy::class);
});


it('can get the attribute by its interface', function () {
    $class = new class () {
        #[WithCast(ConfidentialDataCast::class)]
        protected string $first_name;
    };

    $attributes = DataAttributesCollectionFactory::buildFromReflectionProperty(
        new ReflectionProperty($class, 'first_name')
    );

    expect($attributes->has(GetsCast::class))->toBe(true);
    expect($attributes->first(GetsCast::class))->toBeInstanceOf(WithCast::class);
});
