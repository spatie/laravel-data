<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\PropertyForMorph;
use Spatie\LaravelData\Contracts\PropertyMorphableData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\DataMorphClassResolver;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('returns null for non abstract classes', function () {
    $morph = app(DataMorphClassResolver::class)->execute(
        app(DataConfig::class)->getDataClass(SimpleData::class),
        []
    );

    expect($morph)->toBeNull();
});

it('returns null for non property morphable classes', function () {
    $class = new class () extends Data {
        public string $name;
    };

    $morph = app(DataMorphClassResolver::class)->execute(
        app(DataConfig::class)->getDataClass($class::class),
        []
    );

    expect($morph)->toBeNull();
});

it('can resolve morph class based on properties', function () {
    abstract class TestAbstractMorphableData extends Data implements PropertyMorphableData
    {
        #[PropertyForMorph]
        public string $type;

        public static function morph(array $properties): string
        {
            return $properties['type'];
        }
    };

    $morph = app(DataMorphClassResolver::class)->execute(
        app(DataConfig::class)->getDataClass(TestAbstractMorphableData::class),
        [['type' => 'user']]
    );

    expect($morph)->toBe('user');
});

it('can resolve morph class with mapped input names', function () {
    abstract class TestAbstractMorphableDataWithMappedInputName extends Data implements PropertyMorphableData
    {
        #[PropertyForMorph]
        #[MapInputName('type_for_morph')]
        public string $type;

        public static function morph(array $properties): string
        {
            return $properties['type'];
        }
    };

    $morph = app(DataMorphClassResolver::class)->execute(
        app(DataConfig::class)->getDataClass(TestAbstractMorphableDataWithMappedInputName::class),
        [['type_for_morph' => 'post']]
    );

    expect($morph)->toBe('post');
});

it('can resolve morph class with backed enum type', function () {
    abstract class TestAbstractMorphableDataWithBackedEnum extends Data implements PropertyMorphableData
    {
        #[PropertyForMorph]
        public DummyBackedEnum $type;

        public static function morph(array $properties): string
        {
            return $properties['type']->value;
        }
    };

    $morph = app(DataMorphClassResolver::class)->execute(
        app(DataConfig::class)->getDataClass(TestAbstractMorphableDataWithBackedEnum::class),
        [['type' => DummyBackedEnum::FOO]]
    );

    expect($morph)->toBe(DummyBackedEnum::FOO->value);
});

it('can resolve morph class with backed enum type using default value', function () {
    abstract class TestAbstractMorphableDataWithDefaultValue extends Data implements PropertyMorphableData
    {
        #[PropertyForMorph]
        public DummyBackedEnum $type = DummyBackedEnum::BOO;

        public static function morph(array $properties): ?string
        {
            return $properties['type']->value;
        }
    };

    $morph = app(DataMorphClassResolver::class)->execute(
        app(DataConfig::class)->getDataClass(TestAbstractMorphableDataWithDefaultValue::class),
        [['type' => DummyBackedEnum::BOO]]
    );

    expect($morph)->toBe(DummyBackedEnum::BOO->value);
});


it('can resolve morph class with backed enum type ignoring default value', function () {
    abstract class TestAbstractMorphableDataWithNullableBackedEnum extends Data implements PropertyMorphableData
    {
        #[PropertyForMorph]
        public ?DummyBackedEnum $type = null;

        public static function morph(array $properties): ?string
        {
            return $properties['type']?->value;
        }
    };

    $morph = app(DataMorphClassResolver::class)->execute(
        app(DataConfig::class)->getDataClass(TestAbstractMorphableDataWithNullableBackedEnum::class),
        [['type' => DummyBackedEnum::FOO]]
    );

    expect($morph)->toBe(DummyBackedEnum::FOO->value);
});
