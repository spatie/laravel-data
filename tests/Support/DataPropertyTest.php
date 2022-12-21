<?php

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\DummyModel;
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
    $helper = resolveHelper(new class () {
        #[WithCast(DateTimeInterfaceCast::class, 'd-m-y')]
        public SimpleData $property;
    });

    expect($helper->cast)->toEqual(new DateTimeInterfaceCast('d-m-y'));
});

it('can get the transformer attribute', function () {
    $helper = resolveHelper(new class () {
        #[WithTransformer(DateTimeInterfaceTransformer::class)]
        public SimpleData $property;
    });

    expect($helper->transformer)->toEqual(new DateTimeInterfaceTransformer());
});

it('can get the transformer attribute with arguments', function () {
    $helper = resolveHelper(new class () {
        #[WithTransformer(DateTimeInterfaceTransformer::class, 'd-m-y')]
        public SimpleData $property;
    });

    expect($helper->transformer)->toEqual(new DateTimeInterfaceTransformer('d-m-y'));
});

it('can get the mapped input name', function () {
    $helper = resolveHelper(new class () {
        #[MapInputName('other')]
        public SimpleData $property;
    });

    expect($helper->inputMappedName)->toEqual('other');
});

it('can get the mapped output name', function () {
    $helper = resolveHelper(new class () {
        #[MapOutputName('other')]
        public SimpleData $property;
    });

    expect($helper->outputMappedName)->toEqual('other');
});

it('can get all attributes', function () {
    $helper = resolveHelper(new class () {
        #[MapInputName('other')]
        #[WithTransformer(DateTimeInterfaceTransformer::class)]
        #[WithCast(DateTimeInterfaceCast::class)]
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $property;
    });

    expect($helper->attributes)->toHaveCount(4);
});

it('can get the default value', function () {
    $helper = resolveHelper(new class () {
        public string $property;
    });

    expect($helper->hasDefaultValue)->toBeFalse();

    $helper = resolveHelper(new class () {
        public string $property = 'hello';
    });

    expect($helper)
        ->hasDefaultValue->toBeTrue()
        ->defaultValue->toEqual('hello');
});

it('can check if the property is promoted', function () {
    $helper = resolveHelper(new class ('') {
        public function __construct(
            public string $property,
        ) {
        }
    });

    expect($helper->isPromoted)->toBeTrue();

    $helper = resolveHelper(new class () {
        public string $property;
    });

    expect($helper->isPromoted)->toBeFalse();
});

it('can check if a property should be validated', function () {
    expect(
        resolveHelper(new class () {
            public string $property;
        })->validate
    )->toBeTrue();

    expect(
        resolveHelper(new class () {
            #[WithoutValidation]
            public string $property;
        })->validate
    )->toBeFalse();
});

it('wont throw an error if non existing attribute is used on a data class property', function () {
    expect(NonExistingPropertyAttributeData::from(['property' => 'hello'])->property)->toEqual('hello')
        ->and(PhpStormAttributeData::from(['property' => 'hello'])->property)->toEqual('hello')
        ->and(PhpStormAttributeData::from('{"property": "hello"}')->property)->toEqual('hello')
        ->and(PhpStormAttributeData::from((object) ['property' => 'hello'])->property)->toEqual('hello')
        ->and(ModelWithPhpStormAttributePropertyData::from((new DummyModel())->fill(['id' => 1]))->id)->toEqual(1)
        ->and(ModelWithPromotedPhpStormAttributePropertyData::from((new DummyModel())->fill(['id' => 1]))->id)->toEqual(1);
});

class NonExistingPropertyAttributeData extends Data
{
    #[\Foo\Bar]
    public readonly string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }
}

class PhpStormAttributeData extends Data
{
    #[\JetBrains\PhpStorm\Immutable]
    public readonly string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }
}

class PromotedPhpStormAttributeData extends Data
{
    public function __construct(
        #[\JetBrains\PhpStorm\Immutable]
        public readonly string $property
    )
    {
        //
    }
}

class ModelWithPhpStormAttributePropertyData extends Data
{
    #[\JetBrains\PhpStorm\Immutable]
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function fromDummyModel(DummyModel $model)
    {
        return new self($model->id);
    }
}

class ModelWithPromotedPhpStormAttributePropertyData extends Data
{
    public function __construct(
        #[\JetBrains\PhpStorm\Immutable]
        public int $id
    ) {
    }

    public static function fromDummyModel(DummyModel $model)
    {
        return new self($model->id);
    }
}
