<?php

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

function resolveHelper(
    object $class,
    bool $hasDefaultValue = false,
    mixed $defaultValue = null
): DataProperty {
    $reflectionProperty = new ReflectionProperty($class, 'property');

    return DataProperty::create($reflectionProperty, $hasDefaultValue, $defaultValue);
}

it('can get the cast attribute with arguments', function () {
    $helper = resolveHelper(new class()
    {
        #[WithCast(DateTimeInterfaceCast::class, 'd-m-y')]
        public SimpleData $property;
    });

    expect($helper->cast)->toEqual(new DateTimeInterfaceCast('d-m-y'));
});

it('can get the transformer attribute', function () {
    $helper = resolveHelper(new class()
    {
        #[WithTransformer(DateTimeInterfaceTransformer::class)]
        public SimpleData $property;
    });

    expect($helper->transformer)->toEqual(new DateTimeInterfaceTransformer());
});

it('can get the transformer attribute with arguments', function () {
    $helper = resolveHelper(new class()
    {
        #[WithTransformer(DateTimeInterfaceTransformer::class, 'd-m-y')]
        public SimpleData $property;
    });

    expect($helper->transformer)->toEqual(new DateTimeInterfaceTransformer('d-m-y'));
});

it('can get the mapped input name', function () {
    $helper = resolveHelper(new class()
    {
        #[MapInputName('other')]
        public SimpleData $property;
    });

    expect($helper->inputMappedName)->toEqual('other');
});

it('can get the mapped output name', function () {
    $helper = resolveHelper(new class()
    {
        #[MapOutputName('other')]
        public SimpleData $property;
    });

    expect($helper->outputMappedName)->toEqual('other');
});

it('can get all attributes', function () {
    $helper = resolveHelper(new class()
    {
        #[MapInputName('other')]
        #[WithTransformer(DateTimeInterfaceTransformer::class)]
        #[WithCast(DateTimeInterfaceCast::class)]
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $property;
    });

    expect($helper->attributes)->toHaveCount(4);
});

it('can get the default value', function () {
    $helper = resolveHelper(new class()
    {
        public string $property;
    });

    expect($helper->hasDefaultValue)->toBeFalse();

    $helper = resolveHelper(new class()
    {
        public string $property = 'hello';
    });

    expect($helper)
        ->hasDefaultValue->toBeTrue()
        ->defaultValue->toEqual('hello');
});

it('can check if the property is promoted', function () {
    $helper = resolveHelper(new class('')
    {
        public function __construct(
            public string $property,
        ) {
        }
    });

    expect($helper->isPromoted)->toBeTrue();

    $helper = resolveHelper(new class()
    {
        public string $property;
    });

    expect($helper->isPromoted)->toBeFalse();
});

it('can check if a property should be validated', function () {
    expect(
        resolveHelper(new class()
        {
            public string $property;
        })->validate
    )->toBeTrue();

    expect(
        resolveHelper(new class()
        {
            #[WithoutValidation]
            public string $property;
        })->validate
    )->toBeFalse();
});
