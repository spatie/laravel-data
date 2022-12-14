<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotBuildRelativeRules;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Validation\NestedRulesWithAdditional;
use Spatie\LaravelData\Tests\Fakes\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithExplicitValidationRuleAttributeData;
use Spatie\LaravelData\Tests\TestSupport\DataValidationAsserter;

it('can validate a string', function () {
    $dataClass = new class () extends Data {
        public string $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['property' => 'Hello World'])
        ->assertRules([
            'property' => ['string', 'required'],
        ]);
});

it('can validate a float', function () {
    $dataClass = new class () extends Data {
        public float $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['property' => 10.0])
        ->assertRules([
            'property' => ['numeric', 'required'],
        ]);
});

it('can validate an integer', function () {
    $dataClass = new class () extends Data {
        public int $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['property' => 10.0])
        ->assertRules([
            'property' => ['numeric', 'required'],
        ]);
});

it('can validate an array', function () {
    $dataClass = new class () extends Data {
        public array $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['property' => ['Hello World']])
        ->assertRules([
            'property' => ['array', 'required'],
        ]);
});

it('can validate a bool', function () {
    $dataClass = new class () extends Data {
        public bool $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['property' => true])
        ->assertRules([
            'property' => ['boolean'],
        ]);
});

it('can validate a nullable type', function () {
    $dataClass = new class () extends Data {
        public ?array $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['property' => ['Hello World']])
        ->assertOk(['property' => null])
        ->assertOk([])
        ->assertRules([
            'property' => ['array', 'nullable'],
        ]);
});

it('can validated a property with custom rules', function () {
    $dataClass = new class () extends Data {
        public ?array $property;

        public static function rules(): array
        {
            return [
                'property' => ['array', 'min:5'],
            ];
        }
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'property' => ['array', 'min:5'],
        ]);
});

it('can validate a property with custom rules as string', function () {
    $dataClass = new class () extends Data {
        public ?array $property;

        public static function rules(): array
        {
            return [
                'property' => 'array|min:5',
            ];
        }
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'property' => ['array', 'min:5'],
        ]);
});

it('can validate a property with custom rules as object', function () {
    $dataClass = new class () extends Data {
        public ?array $property;

        public static function rules(): array
        {
            return [
                'property' => [new ArrayType(), new Min(5)],
            ];
        }
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'property' => ['array', 'min:5'],
        ]);
});

it('can validate a property with attributes', function () {
    $dataClass = new class () extends Data {
        #[Min(5)]
        public ?array $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'property' => ['array', 'min:5', 'nullable'],
        ]);
});

it('can validate an optional attribute', function () {
    DataValidationAsserter::for(new class () extends Data {
        public array|Optional $property;
    })
        ->assertOk([])
        ->assertOk(['property' => []])
        ->assertErrors(['property' => null])
        ->assertRules([
            'property' => ['sometimes', 'array'],
        ]);

    DataValidationAsserter::for(new class () extends Data {
        public array|Optional|null $property;
    })
        ->assertOk([])
        ->assertOk(['property' => []])
        ->assertOk(['property' => null])
        ->assertRules([
            'property' => ['sometimes', 'array', 'nullable'],
        ]);

    DataValidationAsserter::for(new class () extends Data {
        #[Max(10)]
        public array|Optional $property;
    })
        ->assertOk([])
        ->assertOk(['property' => []])
        ->assertErrors(['property' => null])
        ->assertRules([
            'property' => ['sometimes', 'array', 'max:10'],
        ]);
});

it('can validate a native enum', function () {
    $dataClass = new class () extends Data {
        public DummyBackedEnum $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['property' => 'foo'])
        ->assertRules([
            'property' => [new Enum(DummyBackedEnum::class), 'required'],
        ]);
});

it('will use name mapping within validation', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('some_property')]
        public string $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['some_property' => 'foo'])
        ->assertRules([
            'some_property' => ['string', 'required'],
        ]);
});

it('can disable validation', function () {
    $dataClass = new class () extends Data {
        #[WithoutValidation]
        public string $property;

        #[DataCollectionOf(SimpleData::class), WithoutValidation]
        public DataCollection $collection;

        #[WithoutValidation]
        public SimpleData $data;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([])
        ->assertRules([]);
});

it('can write custom rules based upon payloads', function () {
    $dataClass = new class () extends Data {
        public bool $strict;

        public string $property;

        #[MapInputName(SnakeCaseMapper::class)]
        public string $mappedProperty;

        public static function rules(array $payload): array
        {
            if ($payload['strict'] === true) {
                return [
                    'property' => ['in:strict'],
                    'mapped_property' => ['in:strict'],
                ];
            }

            return [];
        }
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules(
            rules: [
                'strict' => ['boolean'],
                'property' => ['in:strict'],
                'mapped_property' => ['in:strict'],
            ],
            payload: [
                'strict' => true,
            ]
        )
        ->assertRules(
            rules: [
                'strict' => ['boolean'],
                'property' => ['string', 'required'],
                'mapped_property' => ['string', 'required'],
            ],
            payload: [
                'strict' => false,
            ]
        );
});

it('can validate nested data', function () {
    eval(<<<'PHP'
            use Spatie\LaravelData\Data;
            class NestedClassA extends Data {
                public string $name;
            }
        PHP);

    $dataClass = new class () extends Data {
        public \NestedClassA $nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['nested' => ['name' => 'Hello World']])
        ->assertErrors(['nested' => []])
        ->assertErrors(['nested' => null])
        ->assertRules([
            'nested' => ['required', 'array'],
            'nested.name' => ['string', 'required'],
        ]);
});

it('can validate nested nullable data', function () {
    eval(<<<'PHP'
            use Spatie\LaravelData\Data;
            class NestedClassB extends Data {
                public string $name;
            }
        PHP);

    $dataClass = new class () extends Data {
        public ?\NestedClassB $nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['nested' => ['name' => 'Hello World']])
        ->assertOk(['nested' => ['name' => null]])
        ->assertOk(['nested' => null])
        ->assertOk(['nested' => []])
        ->assertRules([
            'nested' => ['nullable', 'array'],
            'nested.name' => ['nullable', 'string'],
        ]);
});

it('can validate nested optional data', function () {
    eval(<<<'PHP'
            use Spatie\LaravelData\Data;
            class NestedClassC extends Data {
                public string $name;
            }
        PHP);

    $dataClass = new class () extends Data {
        public \NestedClassC|Optional $nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['nested' => ['name' => 'Hello World']])
        ->assertOk(['nested' => null])
        ->assertErrors(['nested' => ['name' => null]])
        ->assertErrors(['nested' => []])
        ->assertRules([
            'nested' => ['sometimes', 'array'],
            'nested.name' => ['nullable', 'string'],
        ]);
})->skip('Failures');

it('can add additional rules to nested data', function () {
    eval(<<<'PHP'
            use Spatie\LaravelData\Attributes\Validation\In;
            use Spatie\LaravelData\Data;
            class NestedClassD extends Data {
                public string $name;
            }
        PHP);

    $dataClass = new class () extends Data {
        #[Min(100)]
        public \NestedClassD|Optional $nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'nested' => ['sometimes', 'array', 'min:100'],
            'nested.name' => ['nullable', 'string'],
        ]);
});

it('will use name mapping with nested objects', function () {
    $dataClass = new class () extends Data {
        #[MapInputName('some_nested')]
        public SimpleData $nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['some_nested' => ['string' => 'Hello World']])
        ->assertRules([
            'some_nested' => ['required', 'array'],
            'some_nested.string' => ['string', 'required'],
        ]);
});

it('can use nested payloads in nested data', function () {
    eval(<<<'PHP'
            use Spatie\LaravelData\Attributes\Validation\In;
            use Spatie\LaravelData\Data;

            class NestedClassF extends Data implements \Spatie\LaravelData\Contracts\RelativeRuleData {
                public bool $strict;

                public string $name;

                public static function rules(array $payload, ?string $path): array {
                    if($payload['strict'] ?? false) {
                        return ['name' => ['in:strict']];
                    }
                    return [];
                }
            }
        PHP);

    $dataClass = new class () extends Data {
        public \NestedClassF $some_nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules(
            rules: [
                'some_nested' => ['required', 'array'],
                'some_nested.strict' => ['boolean'],
                'some_nested.name' => ['in:strict'],
            ],
            payload: [
                'some_nested' => [
                    'strict' => true,
                ],
            ]
        )
        ->assertRules(
            rules: [
                'some_nested' => ['required', 'array'],
                'some_nested.strict' => ['boolean'],
                'some_nested.name' => ['required', 'string'],
            ],
            payload: [
                'some_nested' => [
                    'strict' => false,
                ],
            ]
        );
})->throwsIf(fn() => CannotBuildRelativeRules::shouldThrow(), CannotBuildRelativeRules::class);

test('rules in nested data are rewritten according to their fields', function () {
    // Should we do the same with the `rules` method?
    // Also implement for collections
    eval(<<<'PHP'
            use Spatie\LaravelData\Attributes\Validation\In;
            use Spatie\LaravelData\Attributes\Validation\RequiredIf;
            use Spatie\LaravelData\Attributes\Validation\RequiredWith;
            use Spatie\LaravelData\Data;
            class NestedClassG extends Data {
                public bool $alsoAString;

                #[RequiredIf('alsoAString', true)]
                public string $string;
            }
        PHP);

    $dataClass = new class () extends Data {
        public \NestedClassG $nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([
            'nested' => ['alsoAString' => '0'],
        ])
        ->assertErrors([
            'nested' => ['alsoAString' => '1'],
        ]) // Fails when we prefix the rule with nested.
        ->assertRules(
            rules: [
                'nested' => ['required', 'array'],
                'nested.alsoAString' => ['boolean'],
                'nested.string' => ['required_if:alsoAString,1', 'string'],
            ]
        );
})->skip('Feature to add');

it('will validate a collection', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([
            'collection' => [
                ['string' => 'Never Gonna'],
                ['string' => 'Give You Up'],
            ],
        ])
        ->assertOk(['collection' => []])
        ->assertErrors(['collection' => null])
        ->assertErrors([])
        ->assertErrors([
            'collection' => [
                ['other_string' => 'Hello World'],
            ],
        ])
        ->assertRules([
            'collection' => ['present', 'array'],
            'collection.*.string' => ['string', 'required'],
        ]);
});

it('will validate a nullable collection', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(SimpleData::class)]
        public ?DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([
            'collection' => [
                ['string' => 'Never Gonna'],
                ['string' => 'Give You Up'],
            ],
        ])
        ->assertOk(['collection' => []])
        ->assertOk(['collection' => null])
        ->assertOk([])
        ->assertErrors([
            'collection' => [
                ['other_string' => 'Hello World'],
            ],
        ])
        ->assertRules([
            'collection' => ['nullable', 'array'],
            'collection.*.string' => ['string', 'required'],
        ]);
});

it('will validate an optional collection', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(SimpleData::class)]
        public Optional|DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([
            'collection' => [
                ['string' => 'Never Gonna'],
                ['string' => 'Give You Up'],
            ],
        ])
        ->assertOk(['collection' => []])
        ->assertOk([])
        ->assertErrors(['collection' => null])
        ->assertErrors([
            'collection' => [
                ['other_string' => 'Hello World'],
            ],
        ])
        ->assertRules([
            'collection' => ['sometimes', 'array'],
            'collection.*.string' => ['string', 'required'],
        ]);
});

it('can overwrite collection class rules', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $collection;

        public static function rules(): array
        {
            return [
                'collection' => ['array', 'min:1', 'max:5'],
                'collection.*.string' => ['required', 'string', 'min:100'],
            ];
        }
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'collection' => ['array', 'min:1', 'max:5'],
            'collection.*.string' => ['required', 'string', 'min:100'],
        ]);
});

it('can add collection class rules using attributes', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(SimpleDataWithExplicitValidationRuleAttributeData::class)]
        #[Min(10)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'collection' => ['present', 'array', 'min:10'],
            'collection.*.email' => ['string', 'required', 'email:rfc'],
        ]);
});

/**
 * Complex Examples
 */

it('can nest data in collections', function () {
    eval(<<<'PHP'
            use Spatie\LaravelData\Data;
            class NestedClassE extends Data {
                public string $string;
            }

            class CollectionClassA extends Data {
                public \NestedClassE $nested;
            }
        PHP);

    $dataClass = new class () extends Data {
        #[DataCollectionOf(\CollectionClassA::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'collection' => ['present', 'array'],
            'collection.*.nested' => ['required', 'array'],
            'collection.*.nested.string' => ['required', 'string'],
        ])
        ->assertOk(['collection' => [['nested' => ['string' => 'Hello World']]]]);
});

it('can nest data in collections using relative rule generation', function () {
    eval(<<<'PHP'
        use Spatie\LaravelData\Attributes\Validation\Required;
        use Spatie\LaravelData\Contracts\RelativeRuleData;
        use Spatie\LaravelData\Data;

        class NestedClassH extends Data implements RelativeRuleData {
            public string $string;
            #[Required]
            public bool $isEmail;

            public static function rules(array $payload, ?string $path): array
            {
                if ($payload['isEmail'] ?? false) {
                    return [
                        'string' => ['required', 'string', 'email'],
                    ];
                }
                return [];
            }
        }
    PHP);

    $dataClass = new class () extends Data {
        #[DataCollectionOf(\NestedClassH::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([
            'collection' => [
                ['string' => 'Hello World', 'isEmail' => false],
                ['string' => 'hello@world.test', 'isEmail' => true],
            ]
        ])
        ->assertErrors([
            'collection' => [
                ['string' => 'Invalid Email', 'isEmail' => true],
                ['string' => 'Hello World', 'isEmail' => false],
                ['string' => 'Invalid Email', 'isEmail' => true],
            ]
        ], [
            'collection.0.string' => ['The collection.0.string must be a valid email address.'],
            'collection.2.string' => ['The collection.2.string must be a valid email address.'],
        ]);
})->throwsIf(fn() => CannotBuildRelativeRules::shouldThrow(), CannotBuildRelativeRules::class);

it('can nest data in classes inside collections using relative rule generation', function () {
    eval(<<<'PHP'
        use Spatie\LaravelData\Attributes\Validation\Required;
        use Spatie\LaravelData\Data;
        use Spatie\LaravelData\Contracts\RelativeRuleData;
        class NestedClassJ extends Data implements RelativeRuleData {
            public string $string;
            #[Required]
            public bool $isEmail;

            public static function rules(array $payload, ?string $path): array
            {
                return $payload['isEmail'] ?? false
                    ? ['string' => ['required', 'string', 'email']]
                    : [];
            }
        }

        class CollectionClassK extends Data {
            public \NestedClassJ $nested;
        }
    PHP);

    $dataClass = new class () extends Data {
        #[DataCollectionOf(\CollectionClassK::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'collection' => ['present', 'array'],
            'collection.*.nested.string' => ['required', 'string'],
            'collection.*.nested.isEmail' => ['required', 'boolean'],
            'collection.0.nested' => [],
            'collection.1.nested.string' => ['required', 'string', 'email'],
        ], [
            'collection' => [
                ['nested' => ['string' => 'Hello World', 'isEmail' => false]],
                ['nested' => ['string' => 'hello@world.test', 'isEmail' => true]],
            ],
        ])
        ->assertOk([
            'collection' => [
                ['nested' => ['string' => 'Hello World', 'isEmail' => false]],
                ['nested' => ['string' => 'hello@world.test', 'isEmail' => true]],
            ]
        ])
        ->assertErrors([
            'collection' => [
                ['nested' => ['string' => 'Invalid Email', 'isEmail' => true]],
                ['nested' => ['string' => 'Hello World', 'isEmail' => false]],
                ['nested' => ['string' => 'Invalid Email', 'isEmail' => true]],
            ]
        ], [
            'collection.0.nested.string' => ['The collection.0.nested.string must be a valid email address.'],
            'collection.2.nested.string' => ['The collection.2.nested.string must be a valid email address.'],
        ]);
})->throwsIf(fn() => CannotBuildRelativeRules::shouldThrow(), CannotBuildRelativeRules::class);

it('can nest data in deep collections using relative rule generation', function () {
    eval(<<<'PHP'
        use Spatie\LaravelData\Attributes\DataCollectionOf;
        use Spatie\LaravelData\Attributes\Validation\Required;
        use Spatie\LaravelData\Contracts\RelativeRuleData;
        use Spatie\LaravelData\Data;
        use Spatie\LaravelData\DataCollection;

        class NestedClassL extends Data implements RelativeRuleData {
            public string $deepString;
            #[Required]
            public bool $deepIsEmail;

            public static function rules(array $payload, ?string $path): array
            {
                return $payload['deepIsEmail'] ?? false
                    ? ['deepString' => ['required', 'string', 'email']]
                    : [];
            }
        }

        class NestedClassM extends Data implements RelativeRuleData {
            public string $string;
            #[Required]
            public bool $isEmail;

            #[DataCollectionOf(\NestedClassL::class), Required]
            public DataCollection $items;

            public static function rules(array $payload, ?string $path): array
            {
                return $payload['isEmail'] ?? false
                    ? ['string' => ['required', 'string', 'email']]
                    : [];
            }
        }
    PHP);

    $dataClass = new class () extends Data {
        #[DataCollectionOf(\NestedClassM::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'collection' => [
                'array',
                'present',
            ],
            'collection.*.isEmail' => [
                'boolean',
                'required',
            ],
            'collection.*.items' => [
                'array',
                'present',
                'required',
            ],
            'collection.*.items.*.deepIsEmail' => [
                'boolean',
                'required',
            ],
            'collection.*.items.*.deepString' => [
                'required',
                'string',
            ],
            'collection.*.string' => [
                'required',
                'string',
            ],
            'collection.0' => [],
            'collection.0.items.0' => [],
            'collection.0.items.1.deepString' => [
                'email',
                'required',
                'string',
            ],
        ], [
            'collection' => [
                [
                    'string' => 'Hello World',
                    'isEmail' => false,
                    'items' => [
                        ['deepString' => 'Hello World', 'deepIsEmail' => false],
                        ['deepString' => 'hello@world.test', 'deepIsEmail' => true],
                    ],
                ],
            ],
        ])
        ->assertOk([
            'collection' => [
                [
                    'string' => 'Hello World',
                    'isEmail' => false,
                    'items' => [
                        ['deepString' => 'Hello World', 'deepIsEmail' => false],
                        ['deepString' => 'hello@world.test', 'deepIsEmail' => true],
                    ],
                ],
            ],
        ])
        ->assertErrors([
            'collection' => [
                [
                    'string' => 'Hello World',
                    'isEmail' => false,
                    'items' => [
                        ['deepString' => 'Invalid Email', 'deepIsEmail' => true],
                        ['deepString' => 'Hello World', 'deepIsEmail' => false],
                        ['deepString' => 'hello@world.test', 'deepIsEmail' => true],
                    ],
                ],
                [
                    'string' => 'Invalid Email',
                    'isEmail' => true,
                    'items' => [
                        ['deepString' => 'Invalid Email', 'deepIsEmail' => true],
                        ['deepString' => 'Hello World', 'deepIsEmail' => false],
                        ['deepString' => 'hello@world.test', 'deepIsEmail' => true],
                    ],
                ],
            ]
        ], [
            'collection.1.string' => ['The collection.1.string must be a valid email address.'],
            'collection.0.items.0.deepString' => ['The collection.0.items.0.deepString must be a valid email address.'],
            'collection.1.items.0.deepString' => ['The collection.1.items.0.deepString must be a valid email address.'],
        ]);
})->throwsIf(fn() => CannotBuildRelativeRules::shouldThrow(), CannotBuildRelativeRules::class);

it('can nest data using relative rule generation', function () {
    eval(<<<'PHP'
        use Spatie\LaravelData\Data;
        class NestedClassI extends Data implements \Spatie\LaravelData\Contracts\RelativeRuleData {
            public string $string;
            #[\Spatie\LaravelData\Attributes\Validation\Required]
            public bool $isEmail;

            public static function rules(array $payload, ?string $path): array
            {
                if ($payload['isEmail'] ?? false) {
                    return [
                        'string' => ['required', 'string', 'email'],
                    ];
                }
                return [];
            }
        }
    PHP);

    $dataClass = new class () extends Data {
        public \NestedClassI $nested;
    };

    $payload = [
        'nested' => [
            'string' => 'Hello World',
            'isEmail' => true
        ]
    ];

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'nested' => ['required', 'array'],
            'nested.string' => ['required', 'string', 'email'],
            'nested.isEmail' => ['required', 'boolean'],
        ], $payload)
        ->assertErrors($payload);
})->throwsIf(fn() => CannotBuildRelativeRules::shouldThrow(), CannotBuildRelativeRules::class);
