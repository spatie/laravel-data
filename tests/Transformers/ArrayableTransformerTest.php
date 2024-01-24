<?php

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Transformers\ArrayableTransformer;

it('can transform an arrayable', function () {
    $transformer = new ArrayableTransformer();

    $class = new class (new Collection(['A', 'B'])) extends Data {
        public function __construct(
            public Collection $arrayable,
        ) {
        }
    };

    expect(
        $transformer->transform(
            FakeDataStructureFactory::property($class, 'arrayable'),
            $class->arrayable,
            TransformationContextFactory::create()->get($class)
        )
    )->toEqual(['A', 'B']);
});
