<?php

namespace Spatie\LaravelData\Tests\Support\Transformation;

use Spatie\LaravelData\Support\Partials\Partial;
use Spatie\LaravelData\Support\Partials\PartialsCollection;
use Spatie\LaravelData\Support\Transformation\DataContext;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapType;

it('can serialize and deserialize a data context', function () {
    $context = new DataContext(
        includePartials: PartialsCollection::create(
            Partial::create('basic'),
            Partial::create('permanent', true),
            Partial::createConditional('conditional', fn () => 'Check that this is true'),
            Partial::create('nested.field'),
            Partial::create('*'),
            Partial::create('nested.field.pointed')->next(),
        ),
        excludePartials: null,
        onlyPartials: null,
        exceptPartials: null,
        wrap: new Wrap(type: WrapType::Defined, key: 'key'),
    );

    $serializable = $context->toSerializedArray();

    $serialized = serialize($serializable);

    $unserialized = unserialize($serialized);

    $deserialized = DataContext::fromSerializedArray($unserialized);

    expect($deserialized)
        ->toBeInstanceOf(DataContext::class)
        ->includePartials->toHaveCount(6)
        ->excludePartials->toBeNull()
        ->onlyPartials->toBeNull()
        ->exceptPartials->toBeNull()
        ->wrap->toEqual($context->wrap);

    $partials = $deserialized->includePartials->toArray();
    $expectedPartials = $context->includePartials->toArray();

    expect($partials[0])->toEqual($expectedPartials[0]);
    expect($partials[1])->toEqual($expectedPartials[1]);
    expect($partials[0])->toEqual($expectedPartials[0]);
    expect($partials[3])->toEqual($expectedPartials[3]);
    expect($partials[4])->toEqual($expectedPartials[4]);
    expect($partials[5])->toEqual($expectedPartials[5]);

    expect($partials[2]['condition']())->toEqual($expectedPartials[2]['condition']());
});
