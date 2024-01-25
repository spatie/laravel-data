<?php

namespace Spatie\LaravelData\Tests\Transformers;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Transformers\EnumTransformer;
use Spatie\LaravelData\Transformers\SerializeTransformer;
use Tests\TestCase;

it('can transform using a serializer', function () {
    $transformer = new SerializeTransformer();

    $class = new class () extends Data {
        public DummyBackedEnum $enum = DummyBackedEnum::FOO;
    };

    expect(
        $transformer->transform(
            FakeDataStructureFactory::property($class, 'enum'),
            $class->enum,
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual(serialize(DummyBackedEnum::FOO));
});
