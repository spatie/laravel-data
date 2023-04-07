<?php

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModel;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('keeps track of a global map from attribute', function () {
    $dataClass = DataClass::create(new ReflectionClass(DataWithMapper::class));

    expect($dataClass->properties->get('casedProperty')->inputMappedName)
        ->toEqual('cased_property')
        ->and($dataClass->properties->get('casedProperty')->outputMappedName)
        ->toEqual('cased_property');
});

it('will provide information about special methods', function () {
    $class = DataClass::create(new ReflectionClass(SimpleData::class));

    expect($class->methods)->toHaveKey('fromString')
        ->and($class->methods->get('fromString'))
        ->toBeInstanceOf(DataMethod::class);
});

it('will provide information about the constructor', function () {
    $class = DataClass::create(new ReflectionClass(SimpleData::class));

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
    $properties = DataClass::create(new ReflectionClass($dataClass::class))->properties->values();

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
        ->and(PhpStormClassAttributeData::from((object)['property' => 'hello'])->property)->toEqual('hello')
        ->and(PhpStormClassAttributeData::from('{"property": "hello"}')->property)->toEqual('hello')
        ->and(ModelWithPhpStormAttributeData::from((new DummyModel())->fill(['id' => 1]))->id)->toEqual(1);
});

it('wont create an output name mapping for non mapped properties', function () {
    $mapping = DataClass::create(new ReflectionClass(SimpleData::class))
        ->outputNameMapping
        ->resolve();

    expect($mapping)
        ->mapped->toBeEmpty()
        ->mappedDataObjects->toBeEmpty();
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
