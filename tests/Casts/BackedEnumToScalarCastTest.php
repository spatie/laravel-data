<?php

use Spatie\LaravelData\Casts\BackedEnumToScalarCast;
use Spatie\LaravelData\Exceptions\CannotCastScalarFromEnum;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyUnitEnum;

it('can cast enum to scalar', function (string $methodName) {
    $enum = DummyBackedEnum::FOO;
    $class = new class () {
        public bool|int|float|string|array $value;
    };

    $caster = new BackedEnumToScalarCast($methodName);
    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            $enum,
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toEqual($enum->$methodName);
})->with(['name', 'value']);


it('fails when casting an unit enum', function () {
    $class = new class () {
        public DummyUnitEnum $enum;
    };
    $value = 1.11;
    $caster = new BackedEnumToScalarCast();
    $caster->cast(
        FakeDataStructureFactory::property($class, 'enum'),
        $value,
        [],
        CreationContextFactory::createFromConfig($class::class)->get()
    );
})->throws(
    CannotCastScalarFromEnum::class,
);
