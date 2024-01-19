<?php

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModel;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

it('keeps track of a global map from attribute', function () {
    $dataClass = FakeDataStructureFactory::class(DataWithMapper::class);

    expect($dataClass->properties->get('casedProperty')->inputMappedName)
        ->toEqual('cased_property')
        ->and($dataClass->properties->get('casedProperty')->outputMappedName)
        ->toEqual('cased_property');
});

it('will provide information about special methods', function () {
    $class = FakeDataStructureFactory::class(SimpleData::class);

    expect($class->methods)->toHaveKey('fromString')
        ->and($class->methods->get('fromString'))
        ->toBeInstanceOf(DataMethod::class);
});

it('will provide information about the constructor', function () {
    $class = FakeDataStructureFactory::class(SimpleData::class);

    expect($class->constructorMethod)
        ->not->toBeNull()
        ->toBeInstanceOf(DataMethod::class);
});

it('will populate defaults to properties when they exist ', function () {
    $dataClass = new class ('', '') extends Data {
        public string $property;

        public string $default_property = 'Hello';

        public function __construct(
            public string $promoted_property,
            public string $default_promoted_property = 'Hello Again',
        ) {
        }
    };

    /** @var \Spatie\LaravelData\Support\DataProperty[] $properties */
    $properties = FakeDataStructureFactory::class($dataClass::class)->properties->values();

    expect($properties[0])
        ->name->toEqual('property')
        ->hasDefaultValue->toBeFalse();

    expect($properties[1])
        ->name->toEqual('default_property')
        ->hasDefaultValue->toBeTrue()
        ->defaultValue->toEqual('Hello');

    expect($properties[2])
        ->name->toEqual('promoted_property')
        ->hasDefaultValue->toBeFalse();

    expect($properties[3])
        ->name->toEqual('default_promoted_property')
        ->hasDefaultValue->toBeTrue()
        ->defaultValue->toEqual('Hello Again');
});

it('wont throw an error if a non existing attribute is used on a data class', function () {
    expect(PhpStormClassAttributeData::from(['property' => 'hello'])->property)->toEqual('hello')
        ->and(NonExistingAttributeData::from(['property' => 'hello'])->property)->toEqual('hello')
        ->and(PhpStormClassAttributeData::from((object) ['property' => 'hello'])->property)->toEqual('hello')
        ->and(PhpStormClassAttributeData::from('{"property": "hello"}')->property)->toEqual('hello')
        ->and(ModelWithPhpStormAttributeData::from((new DummyModel())->fill(['id' => 1]))->id)->toEqual(1);
});

it('resolves parent attributes', function () {
    #[MapName(SnakeCaseMapper::class)]
    #[WithTransformer(DateTimeInterfaceTransformer::class, 'd-m-Y')]
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
    class TestRecursiveAttributesParentData extends Data
    {
    }

    class TestRecursiveAttributesChildData extends TestRecursiveAttributesParentData
    {
        public function __construct(
            public DateTimeInterface $dateTime
        ) {
        }
    }

    $dataClass = FakeDataStructureFactory::class(TestRecursiveAttributesChildData::class);

    expect($dataClass->attributes)
        ->toHaveCount(3)
        ->contains(fn ($attribute) => $attribute instanceof MapName)->toBeTrue()
        ->contains(fn ($attribute) => $attribute instanceof WithTransformer)->toBeTrue()
        ->contains(fn ($attribute) => $attribute instanceof WithCast)->toBeTrue();
});

#[\JetBrains\PhpStorm\Immutable]
class PhpStormClassAttributeData extends Data
{
    public readonly string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }
}

#[\Foo\Bar]
class NonExistingAttributeData extends Data
{
    public readonly string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }
}

#[\JetBrains\PhpStorm\Immutable]
class ModelWithPhpStormAttributeData extends Data
{
    public function __construct(
        public int $id
    ) {
    }

    public static function fromDummyModel(DummyModel $model)
    {
        return new self($model->id);
    }
}
