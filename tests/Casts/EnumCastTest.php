<?php

use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\DummyUnitEnum;

beforeEach(function () {
    onlyPHP81();

    $this->caster = new EnumCast();
});

it('can cast enum', function () {
    $class = new class()
    {
        public DummyBackedEnum $enum;
    };

    expect(
        $this->caster->cast(
            DataProperty::create(new ReflectionProperty($class, 'enum')),
            'foo',
            []
        )
    )->toEqual(DummyBackedEnum::FOO);
});

it('fails when it cannot cast an enum from value', function () {
    $class = new class()
    {
        public DummyBackedEnum $enum;
    };

    expect(
        $this->caster->cast(DataProperty::create(new ReflectionProperty($class, 'enum')), 'bar', [])
    )->toEqual(DummyBackedEnum::FOO);
})->throws(Exception::class);

it('fails when casting an unit enum', function () {
    $class = new class()
    {
        public DummyUnitEnum $enum;
    };

    expect(
        $this->caster->cast(DataProperty::create(new ReflectionProperty($class, 'enum')), 'foo', [])
    )->toEqual(Uncastable::create());
});

it('fails with other types', function () {
    $class = new class()
    {
        public int $int;
    };

    expect(
        $this->caster->cast(DataProperty::create(new ReflectionProperty($class, 'int')), 'foo', [])
    )
        ->toEqual(Uncastable::create());
});