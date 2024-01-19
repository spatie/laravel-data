<?php

use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyUnitEnum;

beforeEach(function () {
    $this->caster = new EnumCast();
});

it('can cast enum', function () {
    $class = new class () {
        public DummyBackedEnum $enum;
    };

    expect(
        $this->caster->cast(
            FakeDataStructureFactory::property($class, 'enum'),
            'foo',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toEqual(DummyBackedEnum::FOO);
});

it('fails when it cannot cast an enum from value', function () {
    $class = new class () {
        public DummyBackedEnum $enum;
    };

    expect(
        $this->caster->cast(
            FakeDataStructureFactory::property($class, 'enum'),
            'bar',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toEqual(DummyBackedEnum::FOO);
})->throws(Exception::class);

it('fails when casting an unit enum', function () {
    $class = new class () {
        public DummyUnitEnum $enum;
    };

    expect(
        $this->caster->cast(
            FakeDataStructureFactory::property($class, 'enum'),
            'foo',
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toEqual(Uncastable::create());
});

it('fails with other types', function () {
    $class = new class () {
        public int $int;
    };

    expect(
        $this->caster->cast(
            FakeDataStructureFactory::property($class, 'int'),
            'foo',
            [],
            CreationContextFactory::createFromConfig($class::class)->get(),
        )
    )
        ->toEqual(Uncastable::create());
});
