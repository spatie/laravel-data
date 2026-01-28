<?php

use Spatie\LaravelData\Support\Validation\ValidationPath;

it('expands glob segments in validation paths', function () {
    $payload = [
        'list_items' => [
            ['title' => 'First'],
            ['title' => 'Second'],
        ],
    ];

    $path = ValidationPath::create('list_items.*.title');
    $matches = $path->matchingWildcardPayloadValidationPaths($payload);

    $matchStrings = array_map(fn (ValidationPath $p) => (string) $p, $matches);

    expect($matchStrings)->toEqual([
        'list_items.0.title',
        'list_items.1.title',
    ]);
});

it('expands trailing glob segments in validation paths', function () {
    $payload = [
        'list_items' => [
            'First',
            'Second',
        ],
    ];

    $path = ValidationPath::create('list_items.*');
    $matches = $path->matchingWildcardPayloadValidationPaths($payload);

    $matchStrings = array_map(fn (ValidationPath $p) => (string) $p, $matches);

    expect($matchStrings)->toEqual([
        'list_items.0',
        'list_items.1',
    ]);
});

it('expands deeply nested glob segments in validation paths', function () {
    $payload = [
        'sections' => [
            [
                'items' => [
                    ['name' => 'A'],
                    ['name' => 'B'],
                ],
            ],
            [
                'items' => [
                    ['name' => 'C'],
                ],
            ],
        ],
    ];

    $path = ValidationPath::create('sections.*.items.*.name');
    $matches = $path->matchingWildcardPayloadValidationPaths($payload);

    $matchStrings = array_map(fn (ValidationPath $p) => (string) $p, $matches);

    expect($matchStrings)->toEqual([
        'sections.0.items.0.name',
        'sections.0.items.1.name',
        'sections.1.items.0.name',
    ]);
});
