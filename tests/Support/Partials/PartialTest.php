<?php

use Spatie\LaravelData\Support\Partials\Partial;
use Spatie\LaravelData\Support\Partials\Segments\AllPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\FieldsPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\NestedPartialSegment;

it('can parse partials', function (string $partialString, array $segments) {
    expect(Partial::create($partialString)->segments)->toEqual($segments);
})->with(function () {
    yield from rootPartialsProvider();
    yield from nestedPartialsProvider();
    yield from invalidPartialsProvider();
});

function rootPartialsProvider(): Generator
{
    yield "empty" => [
         '', // partials
         [], // expected
    ];

    yield "root property" => [
         'name', // partials
         [new FieldsPartialSegment(['name'])], // expected
    ];

    yield "root multi-property" => [
         '{name, age}', // partials
         [new FieldsPartialSegment(['name', 'age'])], // expected
    ];

    yield "root star" => [
         '*', // partials
         [new AllPartialSegment()], // expected
    ];
}

function nestedPartialsProvider(): Generator
{
    yield "nested property" => [
         'struct.name', // partials
         [new NestedPartialSegment('struct'), new FieldsPartialSegment(['name'])], // expected
    ];

    yield "nested multi-property" => [
         'struct.{name, age}', // partials
         [new NestedPartialSegment('struct'), new FieldsPartialSegment(['name', 'age'])], // expected
    ];

    yield "nested star" => [
         'struct.*', // partials
         [new NestedPartialSegment('struct'), new AllPartialSegment()], // expected
    ];
}

function invalidPartialsProvider(): Generator
{
    yield "nested property on all" => [
         '*.name', // partials
         [new AllPartialSegment()], // expected
    ];

    yield "nested property on multi-property" => [
         '{name, age}.name', // partials
         [new FieldsPartialSegment(['name', 'age'])], // expected
    ];
}

it('can use the pointer system when ending in a field', function () {
    $partial = new Partial([
        new NestedPartialSegment('struct'),
        new FieldsPartialSegment(['name', 'age']),
    ]);

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe('struct');
    expect($partial->getFields())->toBe(null);

    $partial->next(); // level 1

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(['name', 'age']);

    $partial->rollbackWhenRequired(); // level 0

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe('struct');
    expect($partial->getFields())->toBe(null);

    $partial->next(); // level 1
    $partial->next(); // level 2 - non existing

    expect($partial->isUndefined())->toBeTrue();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 1

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(['name', 'age']);

    $partial->next(); // level 2 - non existing
    $partial->next(); // level 3 - non existing

    expect($partial->isUndefined())->toBeTrue();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 2 - non existing

    expect($partial->isUndefined())->toBeTrue();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 1

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(['name', 'age']);

    $partial->rollbackWhenRequired(); // level 0

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe('struct');
    expect($partial->getFields())->toBe(null);
});

it('can use the pointer system when ending in an all', function () {
    $partial = new Partial([
        new NestedPartialSegment('struct'),
        new AllPartialSegment(),
    ]);

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe('struct');
    expect($partial->getFields())->toBe(null);

    $partial->next(); // level 1

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeTrue();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 0

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe('struct');
    expect($partial->getFields())->toBe(null);

    $partial->next(); // level 1
    $partial->next(); // level 2 - non existing

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeTrue();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 1

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeTrue();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->next(); // level 2 - non existing
    $partial->next(); // level 3 - non existing

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeTrue();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 2 - non existing

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeTrue();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 1

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeTrue();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 0

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe('struct');
    expect($partial->getFields())->toBe(null);
});
