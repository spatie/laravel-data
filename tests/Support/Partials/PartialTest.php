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
