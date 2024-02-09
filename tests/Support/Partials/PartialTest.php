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
        'partials' => '',
        'expected' => [],
    ];

    yield "root property" => [
        'partials' => 'name',
        'expected' => [new FieldsPartialSegment(['name'])],
    ];

    yield "root multi-property" => [
        'partials' => '{name, age}',
        'expected' => [new FieldsPartialSegment(['name', 'age'])],
    ];

    yield "root star" => [
        'partials' => '*',
        'expected' => [new AllPartialSegment()],
    ];
}

function nestedPartialsProvider(): Generator
{
    yield "nested property" => [
        'partials' => 'struct.name',
        'expected' => [new NestedPartialSegment('struct'), new FieldsPartialSegment(['name'])],
    ];

    yield "nested multi-property" => [
        'partials' => 'struct.{name, age}',
        'expected' => [new NestedPartialSegment('struct'), new FieldsPartialSegment(['name', 'age'])],
    ];

    yield "nested star" => [
        'partials' => 'struct.*',
        'expected' => [new NestedPartialSegment('struct'), new AllPartialSegment()],
    ];
}

function invalidPartialsProvider(): Generator
{
    yield "nested property on all" => [
        'partials' => '*.name',
        'expected' => [new AllPartialSegment()],
    ];

    yield "nested property on multi-property" => [
        'partials' => '{name, age}.name',
        'expected' => [new FieldsPartialSegment(['name', 'age'])],
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
