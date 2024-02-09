<?php

use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Casts\UnserializeCast;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;

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
