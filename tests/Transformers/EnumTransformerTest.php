<?php

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Transformers\EnumTransformer;

it('can transform enums', function () {
    $transformer = new EnumTransformer();

    $class = new class () extends Data {
        public DummyBackedEnum $enum = DummyBackedEnum::FOO;
    };

    expect(
        $transformer->transform(
            FakeDataStructureFactory::property($class, 'enum'),
            $class->enum,
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual(DummyBackedEnum::FOO->value);
});
