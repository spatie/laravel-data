<?php

use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Casts\UnserializeCast;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('will unserialize an object', function () {
    $class = new class () {
        public DummyBackedEnum $enum;
    };

    $value = serialize(DummyBackedEnum::FOO);

    $cast = new UnserializeCast();

    expect(
        $cast->cast(
            FakeDataStructureFactory::property($class, 'enum'),
            $value,
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toEqual(DummyBackedEnum::FOO);
});

it('will throw an exception when the unserialization fails', function () {
    $class = new class () {
        public DummyBackedEnum $enum;
    };

    $cast = new UnserializeCast();

    expect(
        fn () => $cast->cast(
            FakeDataStructureFactory::property($class, 'enum'),
            'foo',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toThrow(ErrorException::class);
});

it('can fail silently', function () {
    $class = new class () {
        public DummyBackedEnum $enum;
    };

    $cast = new UnserializeCast(true);

    expect(
        $cast->cast(
            FakeDataStructureFactory::property($class, 'enum'),
            'foo',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBeInstanceOf(Uncastable::class);
});

it('can restrict the classes which can be unserialized', function () {
    $class = new class () {
        public SimpleData $data;
    };

    $value = serialize(new SimpleData('Hello'));

    $context = CreationContextFactory::createFromConfig($class::class)->get();

    expect((new UnserializeCast(allowedClasses: [SimpleData::class]))->cast(
        FakeDataStructureFactory::property($class, 'data'),
        $value,
        [],
        $context
    ))->toEqual(new SimpleData('Hello'));

    expect((new UnserializeCast(allowedClasses: []))->cast(
        FakeDataStructureFactory::property($class, 'data'),
        $value,
        [],
        $context
    ))->toBeInstanceOf(__PHP_Incomplete_Class::class);

    expect((new UnserializeCast(allowedClasses: false))->cast(
        FakeDataStructureFactory::property($class, 'data'),
        $value,
        [],
        $context
    ))->toBeInstanceOf(__PHP_Incomplete_Class::class);
});
