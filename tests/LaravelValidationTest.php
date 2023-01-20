<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

function expectFieldsWithErrors(ValidatorContract $validator, array $fields)
{
    expect(array_keys($validator->errors()->messages()))->tobe($fields);
}

it('null', function () {
    expectFieldsWithErrors(
        Validator::make(
            [],
            [
                'nested' => ['nullable', 'string'],
            ]
        ),
        []
    );

    expectFieldsWithErrors(
        Validator::make(
            [
                'nested' => null,
            ],
            [
                'nested' => ['nullable', 'string'],
            ]
        ),
        []
    );

    expectFieldsWithErrors(
        Validator::make(
            [
                'nested' => 10,
            ],
            [
                'nested' => ['nullable', 'string'],
            ]
        ),
        ['nested']
    );

    expectFieldsWithErrors(
        Validator::make(
            [],
            [
                'nested' => ['nullable', 'required', 'string'],
            ]
        ),
        ['nested']
    );
});

it('null nested', function () {
    expectFieldsWithErrors(
        Validator::make(
            [],
            [
                'nested' => ['nullable', 'array'],
                'nested.string' => ['required', 'string'],
            ]
        ),
        ['nested.string']
    );
});


it('field references', function () {
    // No nested reference to field, so no check
    expectFieldsWithErrors(
        Validator::make(
            [
                'nested' => [
                    'requirement' => 1,
                    'accept' => 0,
                ],
            ],
            [
                'nested' => ['array'],
                'nested.requirement' => ['boolean'],
                'nested.accept' => ['accepted_if:requirement,1'],
            ]
        ),
        []
    );

    // Nested reference to field so check
    expectFieldsWithErrors(
        Validator::make(
            [
                'nested' => [
                    'requirement' => 1,
                    'accept' => 0,
                ],
            ],
            [
                'nested' => ['array'],
                'nested.requirement' => ['boolean'],
                'nested.accept' => ['accepted_if:nested.requirement,1'],
            ]
        ),
        ['nested.accept']
    );

    // No collection reference field so no check
    expectFieldsWithErrors(
        Validator::make(
            [
                'collection' => [
                    [
                        'requirement' => 1,
                        'accept' => 0,
                    ],
                ],
            ],
            [
                'collection' => ['array'],
                'collection.*' => ['array'],
                'collection.*.requirement' => ['boolean'],
                'collection.*.accept' => ['accepted_if:requirement,1'],
            ]
        ),
        []
    );

    // Collection reference field so check
    expectFieldsWithErrors(
        Validator::make(
            [
                'collection' => [
                    [
                        'requirement' => 1,
                        'accept' => 0,
                    ],
                ],
            ],
            [
                'collection' => ['array'],
                'collection.*' => ['array'],
                'collection.*.requirement' => ['boolean'],
                'collection.*.accept' => ['accepted_if:collection.*.requirement,1'],
            ]
        ),
        ['collection.0.accept']
    );

    // Collection absolute reference field so check
    expectFieldsWithErrors(
        Validator::make(
            [
                'collection' => [
                    [
                        'requirement' => 1,
                        'accept' => 0,
                    ],
                ],
            ],
            [
                'collection' => ['array'],
                'collection.*' => ['array'],
                'collection.*.requirement' => ['boolean'],
                'collection.*.accept' => ['accepted_if:collection.0.requirement,1'],
            ]
        ),
        ['collection.0.accept']
    );
});

it('confirmed rule', function () {
    expectFieldsWithErrors(
        Validator::make(
            [
                'password' => 'test',
            ],
            [
                'password' => ['confirmed'],
            ]
        ),
        ['password']
    );

    expectFieldsWithErrors(
        Validator::make(
            [
                'password' => 'test',
                'password_confirmation' => 'test',
            ],
            [
                'password' => ['confirmed'],
            ]
        ),
        []
    );

    expectFieldsWithErrors(
        Validator::make(
            [
                'nested' => ['password' => 'test'],
            ],
            [
                'nested.password' => ['confirmed'],
            ]
        ),
        ['nested.password']
    );

    expectFieldsWithErrors(
        Validator::make(
            [
                'nested' => [
                    'password' => 'test',
                    'password_confirmation' => 'test',
                ],
            ],
            [
                'nested.password' => ['confirmed'],
            ]
        ),
        []
    );

    expectFieldsWithErrors(
        Validator::make(
            [
                'collection' => [['password' => 'test']],
            ],
            [
                'collection.*.password' => ['confirmed'],
            ]
        ),
        ['collection.0.password']
    );

    expectFieldsWithErrors(
        Validator::make(
            [
                'collection' => [
                    [
                        'password' => 'test',
                        'password_confirmation' => 'test',
                    ],
                ],
            ],
            [
                'collection.*.password' => ['confirmed'],
            ]
        ),
        []
    );
});

it('present rule', function () {
    expectFieldsWithErrors(
        Validator::make(
            [],
            [
                'something' => ['present'],
            ]
        ),
        ['something']
    );

    expectFieldsWithErrors(
        Validator::make(
            [
                'something' => '',
            ],
            [
                'something' => ['present'],
            ]
        ),
        []
    );

    expectFieldsWithErrors(
        Validator::make(
            [
                'something' => null,
            ],
            [
                'something' => ['present', 'array'],
            ]
        ),
        ['something']
    );

    expectFieldsWithErrors(
        Validator::make(
            [
                'something' => '',
            ],
            [
                'something' => ['present', 'array'],
            ]
        ),
        []
    );

    expectFieldsWithErrors(
        Validator::make(
            [
                'something' => [],
            ],
            [
                'something' => ['present', 'array'],
            ]
        ),
        []
    );
});
