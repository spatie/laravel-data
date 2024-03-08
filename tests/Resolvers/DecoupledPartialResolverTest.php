<?php


use Spatie\LaravelData\Resolvers\DecoupledPartialResolver;
use Spatie\LaravelData\Support\Partials\Partial;
use Spatie\LaravelData\Support\Partials\Segments\AllPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\FieldsPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\NestedPartialSegment;

it('can decouple a partial with no further fields, resulting in null', function () {
    $decoupled = Partial::create('name')->next();

    expect((new DecoupledPartialResolver())->execute($decoupled))->toBeNull();
});

it('can decouple a partial with a single field', function () {
    $decoupled = Partial::create('struct.name')->next();

    expect((new DecoupledPartialResolver())->execute($decoupled))
        ->segments->toEqual([new FieldsPartialSegment(['name'])])
        ->pointer->toBe(0)
        ->segmentCount->toBe(1);
});

it('can decouple a partial with a nested field', function () {
    $decoupled = Partial::create('struct.nested.name')
        ->next();

    expect((new DecoupledPartialResolver())->execute($decoupled))
        ->segments->toEqual([new NestedPartialSegment('nested'), new FieldsPartialSegment(['name'])])
        ->pointer->toBe(0)
        ->segmentCount->toBe(2);
});

it('can decouple a partial with an all field', function () {
    $decoupled = Partial::create('struct.*')
        ->next();

    expect((new DecoupledPartialResolver())->execute($decoupled))
        ->segments->toEqual([new AllPartialSegment()])
        ->pointer->toBe(0)
        ->segmentCount->toBe(1)
        ->endsInAll->toBeTrue();
});

it('can decouple a partial with an all field a few levels deep', function () {
    $decoupled = Partial::create('struct.*')
        ->next();

    expect((new DecoupledPartialResolver())->execute($decoupled))
        ->segments->toEqual([new AllPartialSegment()])
        ->pointer->toBe(0)
        ->segmentCount->toBe(1)
        ->endsInAll->toBeTrue();

    $decoupled = Partial::create('struct.*')
        ->next()
        ->next();

    expect((new DecoupledPartialResolver())->execute($decoupled))
        ->segments->toEqual([new AllPartialSegment()])
        ->pointer->toBe(0)
        ->segmentCount->toBe(1)
        ->endsInAll->toBeTrue();

    $decoupled = Partial::create('struct.*')
        ->next()
        ->next()
        ->next();

    expect((new DecoupledPartialResolver())->execute($decoupled))
        ->segments->toEqual([new AllPartialSegment()])
        ->pointer->toBe(0)
        ->segmentCount->toBe(1)
        ->endsInAll->toBeTrue();
});

it('can decouple a partial which is undefined, resulting in null', function () {
    $decoupled = Partial::create('struct.name')
        ->next()
        ->next();

    expect((new DecoupledPartialResolver())->execute($decoupled))
        ->toBeNull();
});
