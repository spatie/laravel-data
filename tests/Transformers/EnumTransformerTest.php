<?php

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Transformers\EnumTransformer;

it('can transform enums', function () {
    onlyPHP81();

    $transformer = new EnumTransformer();

    $class = new class () {
        public DummyBackedEnum $enum = DummyBackedEnum::FOO;
    };

    expect(
        $transformer->transform(
            DataProperty::create(new ReflectionProperty($class, 'enum')),
            $class->enum
        )
    )->toEqual(DummyBackedEnum::FOO->value);
});
