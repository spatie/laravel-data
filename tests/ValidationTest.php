<?php

namespace Spatie\LaravelData\Tests;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists as LaravelExists;
use Illuminate\Validation\Rules\In as LaravelIn;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;
use function PHPUnit\Framework\assertFalse;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\MergeValidationRules;
use Spatie\LaravelData\Attributes\PropertyForMorph;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Bail;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\RequiredIf;
use Spatie\LaravelData\Attributes\Validation\RequiredUnless;
use Spatie\LaravelData\Attributes\Validation\RequiredWith;
use Spatie\LaravelData\Attributes\Validation\RequiredWithout;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Contracts\PropertyMorphableData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\ValidationStrategy;
use Spatie\LaravelData\Support\Validation\References\AuthenticatedUserReference;
use Spatie\LaravelData\Support\Validation\References\ContainerReference;
use Spatie\LaravelData\Support\Validation\References\FieldReference;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\LaravelData\Tests\Fakes\CircData;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\DataWithReferenceFieldValidationAttribute;
use Spatie\LaravelData\Tests\Fakes\DummyDataWithContextOverwrittenValidationRules;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\FakeAuthenticatable;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModel;
use Spatie\LaravelData\Tests\Fakes\MultiData;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\NestedNullableData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithExplicitValidationRuleAttributeData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithOverwrittenRules;
use Spatie\LaravelData\Tests\Fakes\Support\FakeInjectable;
use Spatie\LaravelData\Tests\Fakes\ValidationAttributes\PassThroughCustomValidationAttribute;
use Spatie\LaravelData\Tests\TestSupport\DataValidationAsserter;

it('can validate a string', function () {
    $dataClass = new class () extends Data {
        public string $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['property' => 'Hello World'])
        ->assertRules([
            'property' => ['required', 'string'],
        ]);
});

it('can validate a float', function () {
    $dataClass = new class () extends Data {
        public float $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['property' => 10.0])
        ->assertRules([
            'property' => ['required', 'numeric'],
        ]);
});

it('can validate an integer', function () {
    $dataClass = new class () extends Data {
        public int $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['property' => 10.0])
        ->assertRules([
            'property' => ['required', 'numeric'],
        ]);
});

it('can validate an array', function () {
    $dataClass = new class () extends Data {
        public array $property;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['property' => ['Hello World']])
        ->assertRules([
            'property' => ['required', 'array'],
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
            'property' => ['nullable', 'array'],
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
            'property' => ['nullable', 'array', 'min:5'],
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
            'property' => ['nullable', 'sometimes', 'array'],
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
            'property' => ['required', new Enum(DummyBackedEnum::class)],
        ]);
});

it('will never add extra require rules when not required', function () {
    DataValidationAsserter::for(new class () extends Data {
        public ?string $property;
    })->assertRules([
        'property' => [new Nullable(), 'string'],
    ]);

    DataValidationAsserter::for(new class () extends Data {
        public bool $property;
    })->assertRules([
        'property' => ['boolean'],
    ]);

    DataValidationAsserter::for(new class () extends Data {
        #[RequiredWith('other')]
        public string $property;
    })->assertRules([
        'property' => ['string', 'required_with:other'],
    ]);

    DataValidationAsserter::for(new class () extends Data {
        #[\Spatie\LaravelData\Attributes\Validation\Rule('required_with:other')]
        public string $property;
    })->assertRules([
        'property' => ['string', 'required_with:other'],
    ]);
});

it('is possible to have multiple required rules', function () {
    DataValidationAsserter::for(new class () extends Data {
        #[RequiredUnless('is_required', false), RequiredWith('make_required')]
        public string $property;

        public string $make_required;

        public bool $is_required;
    })->assertRules([
        'property' => ['string', 'required_unless:is_required', 'required_with:make_required'],
        'make_required' => ['required', 'string'],
        'is_required' => ['boolean'],
    ]);
})->skip('Add a new ruleinferrer to rule them all and make these cases better');

it('will take care of mapping', function () {
    DataValidationAsserter::for(new class () extends Data {
        #[MapInputName('some_property')]
        public string $property;
    })
        ->assertOk(['some_property' => 'foo'])
        ->assertErrors(['property' => 'foo'])
        ->assertRules([
            'some_property' => ['required', 'string'],
        ]);

    DataValidationAsserter::for(new class () extends Data {
        #[MapName('some_property')]
        public string $property;
    })
        ->assertOk(['some_property' => 'foo'])
        ->assertErrors(['property' => 'foo'])
        ->assertRules([
            'some_property' => ['required', 'string'],
        ]);


    DataValidationAsserter::for(new class () extends Data {
        #[MapName('some_property')]
        public SimpleData $property;
    })
        ->assertOk(['some_property' => ['string' => 'hi']])
        ->assertErrors(['property' => ['string' => 'hi']])
        ->assertRules([
            'some_property' => ['required', 'array'],
            'some_property.string' => ['required', 'string'],
        ]);

    DataValidationAsserter::for(new class () extends Data {
        #[DataCollectionOf(SimpleData::class), MapName('some_property')]
        public DataCollection $property;
    })
        ->assertOk(['some_property' => [['string' => 'hi']]])
        ->assertErrors(['property' => [['string' => 'hi']]])
        ->assertRules([
            'some_property' => ['present', 'array'],
            'some_property.0.string' => ['required', 'string'],
        ], payload: ['some_property' => [[]]]);

    DataValidationAsserter::for(new class () extends Data {
        #[MapName('some_property')]
        public DataWithMapper $property;
    })
        ->assertOk([
            'some_property' => [
                'cased_property' => 'Hi',
                'data_cased_property' => ['string' => 'Hi'],
                'data_collection_cased_property' => [
                    ['string' => 'Hi'],
                ],
            ],
        ])
        ->assertErrors([
            'property' => [
                'cased_property' => 'Hi',
                'data_cased_property' => ['string' => 'Hi'],
                'data_collection_cased_property' => [
                    ['string' => 'Hi'],
                ],
            ],
        ])
        ->assertErrors([
            'some_property' => [
                'casedProperty' => 'Hi',
                'dataCasedProperty' => ['string' => 'Hi'],
                'dataCollectionCasedProperty' => [
                    ['string' => 'Hi'],
                ],
            ],
        ])
        ->assertRules([
            'some_property' => ['required', 'array'],
            'some_property.cased_property' => ['required', 'string'],
            'some_property.data_cased_property' => ['required', 'array'],
            'some_property.data_cased_property.string' => ['required', 'string'],
            'some_property.data_collection_cased_property' => ['present', 'array'],
            'some_property.data_collection_cased_property.0.string' => ['required', 'string'],
        ], payload: [
            'some_property' => [
                'data_collection_cased_property' => [[]],
            ],
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

        public static function rules(ValidationContext $context): array
        {
            if ($context->payload['strict'] === true) {
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
                'property' => ['required', 'string'],
                'mapped_property' => ['required', 'string'],
            ],
            payload: [
                'strict' => false,
            ]
        );
});


it('can write custom rules based upon injected dependencies', function () {
    $dataClass = new class () extends Data {
        public string $environment;

        public static function rules(Application $app): array
        {
            return [
                'environment' => [new Required(), new StringType(), In::create($app->environment())],
            ];
        }
    };

    DataValidationAsserter::for($dataClass)->assertRules([
        'environment' => ['required', 'string', 'in:"testing"'],
    ]);
});

it('can validate nested data', function () {
    $dataClass = new class () extends Data {
        public SimpleData $nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['nested' => ['string' => 'Hello World']])
        ->assertErrors(['nested' => []])
        ->assertErrors(['nested' => null])
        ->assertRules([
            'nested' => ['required', 'array'],
            'nested.string' => ['required', 'string'],
        ]);
});

it('can validate nested nullable data', function () {
    $dataClass = new class () extends Data {
        public ?SimpleData $nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['nested' => ['string' => 'Hello World']])
        ->assertOk(['nested' => null])
        ->assertErrors(['nested' => ['string' => null]])
        ->assertErrors(['nested' => []])
        ->assertRules(['nested' => ['nullable', 'array']], payload: [])
        ->assertRules([
            'nested' => ['nullable', 'array'],
            'nested.string' => ['required', 'string'],
        ], payload: ['nested' => []]);
});

it('can validate nested optional data', function () {
    $dataClass = new class () extends Data {
        public SimpleData|Optional $nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['nested' => ['string' => 'Hello World']])
        ->assertErrors(['nested' => null])
        ->assertErrors(['nested' => ['string' => null]])
        ->assertErrors(['nested' => []])
        ->assertRules([
            'nested' => ['sometimes', 'array'],
        ], payload: [])
        ->assertRules([
            'nested' => ['sometimes', 'array'],
            'nested.string' => ['required', 'string'],
        ], ['nested' => null]);
});

it('can add additional rules to nested data', function () {
    $dataClass = new class () extends Data {
        #[Bail]
        public SimpleData $nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'nested' => ['required', 'array', 'bail'],
            'nested.string' => ['required', 'string'],
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
            'some_nested.string' => ['required', 'string'],
        ]);
});

it('can use nested payloads in nested data', function () {
    $dataClass = new class () extends Data {
        public DummyDataWithContextOverwrittenValidationRules $some_nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules(
            rules: [
                'some_nested' => ['required', 'array'],
                'some_nested.validate_as_email' => ['boolean', 'required'],
                'some_nested.string' => ['required', 'string', 'email'],
            ],
            payload: [
                'some_nested' => [
                    'validate_as_email' => true,
                ],
            ]
        )
        ->assertRules(
            rules: [
                'some_nested' => ['required', 'array'],
                'some_nested.validate_as_email' => ['boolean', 'required'],
                'some_nested.string' => ['required', 'string'],
            ],
            payload: [
                'some_nested' => [
                    'validate_as_email' => false,
                ],
            ]
        );
});

test('can use a reference to another field in data', function () {
    DataValidationAsserter::for(DataWithReferenceFieldValidationAttribute::class)
        ->assertOk([
            'check_string' => '0',
        ])
        ->assertErrors([
            'check_string' => '1',
        ])
        ->assertRules(
            rules: [
                'check_string' => ['boolean'],
                'string' => ['string', 'required_if:check_string,1'],
            ],
            payload: [
                'check_string' => '1',
            ]
        );
});

test('can use a reference to another field in nested data', function () {
    $dataClass = new class () extends Data {
        public DataWithReferenceFieldValidationAttribute $nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([
            'nested' => ['check_string' => '0'],
        ])
        ->assertErrors([
            'nested' => ['check_string' => '1'],
        ])
        ->assertRules(
            rules: [
                'nested' => ['required', 'array'],
                'nested.check_string' => ['boolean'],
                'nested.string' => ['string', 'required_if:nested.check_string,1'],
            ],
            payload: [
                'nested' => ['check_string' => '1'],
            ]
        );
});

test('can use a reference to another field in a collection', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(DataWithReferenceFieldValidationAttribute::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([
            'collection' => [
                ['check_string' => '0'],
            ],
        ])
        ->assertErrors([
            'collection' => [
                ['check_string' => '1'],
            ],
        ])
        ->assertRules(
            rules: [
                'collection' => ['present', 'array'],
                'collection.0.check_string' => ['boolean'],
                'collection.0.string' => ['string', 'required_if:collection.0.check_string,1'],
            ],
            payload: [
                'collection' => [['check_string' => '1']],
            ]
        );
});

test('can use a reference to another field in a collection with nested data', function () {
    class TestValidationDataWithCollectionNestedDataWithFieldReference extends Data
    {
        public DataWithReferenceFieldValidationAttribute $nested;
    }

    $dataClass = new class () extends Data {
        #[DataCollectionOf(TestValidationDataWithCollectionNestedDataWithFieldReference::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([
            'collection' => [
                ['nested' => ['check_string' => '0']],
            ],
        ])
        ->assertErrors([
            'collection' => [
                ['nested' => ['check_string' => '1']],
            ],
        ])
        ->assertRules(
            rules: [
                'collection' => ['present', 'array'],
                'collection.0.nested' => ['required', 'array'],
                'collection.0.nested.check_string' => ['boolean'],
                'collection.0.nested.string' => ['string', 'required_if:collection.0.nested.check_string,1'],
            ],
            payload: [
                'collection' => [
                    ['nested' => ['check_string' => '1']],
                ],
            ]
        );
});

it('can reference to the root validated object in nested data', function () {
    class TestDataWithRootReferenceFieldValidationAttribute extends Data
    {
        #[RequiredIf(new FieldReference('check_string', true), true)]
        public string $string;
    }

    $dataClass = new class () extends Data {
        public bool $check_string;

        public TestDataWithRootReferenceFieldValidationAttribute $nested;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([
            'check_string' => '0',
            'nested' => ['something'],
        ])
        ->assertErrors([
            'check_string' => '1',
            'nested' => ['something'],
        ])
        ->assertRules(
            rules: [
                'check_string' => ['boolean'],
                'nested' => ['required', 'array'],
                'nested.string' => ['string', 'required_if:check_string,1'],
            ],
            payload: [
                'nested' => ['check_string' => '1'],
            ]
        );
});

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
        ->assertErrors(['collection' => ['strings', 'here', 'instead', 'of', 'arrays']])
        ->assertErrors([
            'collection' => [
                ['other_string' => 'Hello World'],
            ],
        ])
        ->assertRules([
            'collection' => ['present', 'array'],
        ])
        ->assertRules([
            'collection' => ['present', 'array'],
            'collection.0.string' => ['required', 'string'],
        ], [
            'collection' => [[]],
        ]);
});

it('will validate collection with explicit require', function () {
    $dataClass = new class () extends Data {
        #[Required]
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
        ->assertErrors(['collection' => []])
        ->assertErrors(['collection' => null])
        ->assertErrors([])
        ->assertErrors([
            'collection' => [
                ['other_string' => 'Hello World'],
            ],
        ])
        ->assertRules([
            'collection' => ['present', 'array', 'required'],
        ])
        ->assertRules([
            'collection' => ['present', 'array', 'required'],
            'collection.0.string' => ['required', 'string'],
        ], [
            'collection' => [[]],
        ]);
});


it('will validate a collection with extra attributes', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(SimpleDataWithExplicitValidationRuleAttributeData::class)]
        #[Min(2)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([
            'collection' => [
                ['email' => 'ruben@spatie.be'],
                ['email' => 'freek@spatie.be'],
            ],
        ])
        ->assertErrors([
            'collection' => [
                ['email' => 'not-an'],
                ['email' => 'email-address'],
            ],
        ])
        ->assertErrors(['collection' => []])
        ->assertRules([
            'collection' => ['present', 'array', 'min:2'],
        ])
        ->assertRules([
            'collection' => ['present', 'array', 'min:2'],
            'collection.0.email' => ['required', 'string', 'email:rfc'],
        ], [
            'collection' => [[]],
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
        ->assertRules(['collection' => ['nullable', 'array']], payload: [])
        ->assertRules(['collection' => ['nullable', 'array']], payload: ['collection' => null])
        ->assertRules([
            'collection' => ['nullable', 'present', 'array'],
            'collection.0.string' => ['required', 'string'],
        ], payload: [
            'collection' => [[]],
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
            'collection' => ['sometimes', 'present', 'array'],
        ], payload: ['collection' => null])
        ->assertRules([
            'collection' => ['sometimes', 'present', 'array'],
            'collection.0.string' => ['required', 'string'],
        ], payload: [
            'collection' => [[]],
        ]);
});

it('can overwrite collection class rules', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $collection;

        public static function rules(): array
        {
            return [
                'collection' => ['array', 'min:1', 'max:2'],
                // TODO: should we allow this, how to handle this?
//                'collection.*.string' => ['required', 'string', 'min:100'],
            ];
        }
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([
            'collection' => [
                ['string' => 'Never Gonna'],
                ['string' => 'Give You Up'],
            ],
        ])
        ->assertOk([
            'collection' => [
                ['string' => 'Never Gonna'],
            ],
        ])
        ->assertErrors([
            'collection' => [
                ['string' => 'Never Gonna'],
                ['string' => 'Give You Up'],
                ['string' => 'Never Gonna'],
            ],
        ])
        ->assertErrors(['collection' => []])
        ->assertRules([
            'collection' => ['array', 'min:1', 'max:2'],
        ], payload: [])
        ->assertRules([
            'collection' => ['array', 'min:1', 'max:2'],
            'collection.0.string' => ['required', 'string'],
        ], payload: [
            'collection' => [[]],
        ]);
});

/**
 * Complex Examples
 */

it('can nest data in collections', function () {
    class CollectionClassA extends Data
    {
        public SimpleData $nested;
    }

    $dataClass = new class () extends Data {
        #[DataCollectionOf(CollectionClassA::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['collection' => [['nested' => ['string' => 'Hello World']]]])
        ->assertErrors(['collection' => [['nested' => null]]])
        ->assertErrors(['collection' => [['nested' => []]]])
        ->assertRules([
            'collection' => ['present', 'array'],
        ])
        ->assertRules([
            'collection' => ['present', 'array'],
            'collection.0.nested' => ['required', 'array'],
            'collection.0.nested.string' => ['required', 'string'],
        ], [
            'collection' => [[]],
        ]);
});

it('can nest nullable data in collections', function () {
    class CollectionClassTable extends Data
    {
        public ?SimpleData $nested;
    }

    $dataClass = new class () extends Data {
        #[DataCollectionOf(CollectionClassTable::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['collection' => [['nested' => ['string' => 'Hello World']]]])
        ->assertRules([
            'collection' => ['present', 'array'],
        ])
        ->assertRules([
            'collection' => ['present', 'array'],
            'collection.0.nested' => ['nullable', 'array'],
        ], [
            'collection' => [[]],
        ])
        ->assertRules([
            'collection' => ['present', 'array'],
            'collection.0.nested' => ['nullable', 'array'],
        ], [
            'collection' => [['nested' => null]],
        ])
        ->assertRules([
            'collection' => ['present', 'array'],
            'collection.0.nested' => ['nullable', 'array'],
            'collection.0.nested.string' => ['required', 'string'],
        ], [
            'collection' => [['nested' => []]],
        ]);
});


it('can nest optional data in collections', function () {
    class CollectionClassC extends Data
    {
        public Optional|SimpleData $nested;
    }

    $dataClass = new class () extends Data {
        #[DataCollectionOf(CollectionClassC::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['collection' => [['nested' => ['string' => 'Hello World']]]])
        ->assertErrors(['collection' => [['nested' => null]]])
        ->assertErrors(['collection' => [['nested' => []]]])
        ->assertRules([
            'collection' => ['present', 'array'],
        ])
        ->assertRules([
            'collection' => ['present', 'array'],
            'collection.0.nested' => ['sometimes', 'array'],
        ], [
            'collection' => [[]],
        ])
        ->assertRules([
            'collection' => ['present', 'array'],
            'collection.0.nested' => ['sometimes', 'array'],
            'collection.0.nested.string' => ['required', 'string'],
        ], [
            'collection' => [['nested' => null]],
        ])
        ->assertRules([
            'collection' => ['present', 'array'],
            'collection.0.nested' => ['sometimes', 'array'],
            'collection.0.nested.string' => ['required', 'string'],
        ], [
            'collection' => [['nested' => []]],
        ]);
});

it('can nest data in collections using relative rule generation', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(DummyDataWithContextOverwrittenValidationRules::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([
            'collection' => [
                ['string' => 'Hello World', 'validate_as_email' => false],
                ['string' => 'hello@world.test', 'validate_as_email' => true],
            ],
        ])
        ->assertErrors([
            'collection' => [
                ['string' => 'Invalid Email', 'validate_as_email' => true],
                ['string' => 'Hello World', 'validate_as_email' => false],
                ['string' => 'Invalid Email', 'validate_as_email' => true],
            ],
        ], [
            'collection.0.string' => [__('validation.email', ['attribute' => 'collection.0.string'])],
            'collection.2.string' => [__('validation.email', ['attribute' => 'collection.2.string'])],
        ])
        ->assertRules(
            [
                'collection' => ['present', 'array'],
                'collection.0.string' => ['required', 'string'],
                'collection.0.validate_as_email' => ['boolean', 'required'],
                'collection.1.string' => ['required', 'string', 'email'],
                'collection.1.validate_as_email' => ['boolean', 'required'],
            ],
            [
                'collection' => [
                    ['string' => 'Hello World', 'validate_as_email' => false],
                    ['string' => 'hello@world.test', 'validate_as_email' => true],
                ],
            ]
        );
});

it('supports required without validation for optional collections', function () {
    $dataClass = new class () extends Data {
        #[RequiredWithout('someOtherData')]
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection|Optional $someData;

        #[RequiredWithout('someData')]
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection|Optional $someOtherData;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules(
            [
                'someData' => [
                    'array',
                    'required_without:someOtherData',
                ],
                'someOtherData' => [
                    'array',
                    'required_without:someData',
                ],
            ],
        )
        ->assertOk([
            'someData' => [["string" => "Hello World"]],
        ])
        ->assertOk([
            'someOtherData' => [["string" => "Hello World"]],
        ])
        ->assertOk([
            'someData' => [["string" => "Hello World"]],
            'someOtherData' => [["string" => "Hello World"]],
        ])
        ->assertErrors([]);
});

it('supports required without validation for nullable collections', function () {
    $dataClass = new class () extends Data {
        #[RequiredWithout('someOtherData')]
        #[DataCollectionOf(SimpleData::class)]
        public ?DataCollection $someData;

        #[RequiredWithout('someData')]
        #[DataCollectionOf(SimpleData::class)]
        public ?DataCollection $someOtherData;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules(
            [
                'someData' => [
                    'nullable',
                    'array',
                    'required_without:someOtherData',
                ],
                'someOtherData' => [
                    'nullable',
                    'array',
                    'required_without:someData',
                ],
            ],
            []
        );
});

it('can nest data in classes inside collections using relative rule generation', function () {
    class CollectionClassK extends Data
    {
        public DummyDataWithContextOverwrittenValidationRules $nested;
    }

    $dataClass = new class () extends Data {
        #[DataCollectionOf(CollectionClassK::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'collection' => ['present', 'array'],
            'collection.0.nested' => ['required', 'array'],
            'collection.0.nested.string' => ['required', 'string'],
            'collection.0.nested.validate_as_email' => ['boolean', 'required'],
            'collection.1.nested' => ['required', 'array'],
            'collection.1.nested.string' => ['required', 'string', 'email'],
            'collection.1.nested.validate_as_email' => ['boolean', 'required'],
        ], [
            'collection' => [
                ['nested' => ['string' => 'Hello World', 'validate_as_email' => false]],
                ['nested' => ['string' => 'hello@world.test', 'validate_as_email' => true]],
            ],
        ])
        ->assertOk([
            'collection' => [
                ['nested' => ['string' => 'Hello World', 'validate_as_email' => false]],
                ['nested' => ['string' => 'hello@world.test', 'validate_as_email' => true]],
            ],
        ])
        ->assertErrors([
            'collection' => [
                ['nested' => ['string' => 'Invalid Email', 'validate_as_email' => true]],
                ['nested' => ['string' => 'Hello World', 'validate_as_email' => false]],
                ['nested' => ['string' => 'Invalid Email', 'validate_as_email' => true]],
            ],
        ], [
            'collection.0.nested.string' => [__('validation.email', ['attribute' => 'collection.0.nested.string'])],
            'collection.2.nested.string' => [__('validation.email', ['attribute' => 'collection.2.nested.string'])],
        ]);
});

it('can nest data in deep collections using relative rule generation', function () {
    class ValidationTestDeepNestedDataWithContextOverwrittenRules extends Data
    {
        public string $deep_string;

        #[Required]
        public bool $deep_validate_as_email;

        public static function rules(ValidationContext $context): array
        {
            return $context->payload['deep_validate_as_email'] ?? false
                ? ['deep_string' => ['required', 'string', 'email']]
                : [];
        }
    }

    class ValidationTestNestedDataWithContextOverwrittenRules extends Data
    {
        public string $string;

        #[Required]
        public bool $validate_as_email;

        #[DataCollectionOf(ValidationTestDeepNestedDataWithContextOverwrittenRules::class), Required]
        public DataCollection $items;

        public static function rules(ValidationContext $context): array
        {
            return $context->payload['validate_as_email'] ?? false
                ? ['string' => ['required', 'string', 'email']]
                : [];
        }
    }

    $dataClass = new class () extends Data {
        #[DataCollectionOf(ValidationTestNestedDataWithContextOverwrittenRules::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'collection' => ['present', 'array',],
            'collection.0.validate_as_email' => ['boolean', 'required'],
            'collection.0.string' => ['required', 'string'],
            'collection.0.items' => ['present', 'array', 'required'],
            'collection.0.items.0.deep_validate_as_email' => ['boolean', 'required'],
            'collection.0.items.0.deep_string' => ['required', 'string'],
            'collection.0.items.1.deep_validate_as_email' => ['boolean', 'required'],
            'collection.0.items.1.deep_string' => ['required', 'string', 'email'],
        ], [
            'collection' => [
                [
                    'string' => 'Hello World',
                    'validate_as_email' => false,
                    'items' => [
                        ['deep_string' => 'Hello World', 'deep_validate_as_email' => false],
                        ['deep_string' => 'hello@world.test', 'deep_validate_as_email' => true],
                    ],
                ],
            ],
        ])
        ->assertOk([
            'collection' => [
                [
                    'string' => 'Hello World',
                    'validate_as_email' => false,
                    'items' => [
                        ['deep_string' => 'Hello World', 'deep_validate_as_email' => false],
                        ['deep_string' => 'hello@world.test', 'deep_validate_as_email' => true],
                    ],
                ],
            ],
        ])
        ->assertErrors([
            'collection' => [
                [
                    'string' => 'Hello World',
                    'validate_as_email' => false,
                    'items' => [
                        ['deep_string' => 'Invalid Email', 'deep_validate_as_email' => true],
                        ['deep_string' => 'Hello World', 'deep_validate_as_email' => false],
                        ['deep_string' => 'hello@world.test', 'deep_validate_as_email' => true],
                    ],
                ],
                [
                    'string' => 'Invalid Email',
                    'validate_as_email' => true,
                    'items' => [
                        ['deep_string' => 'Invalid Email', 'deep_validate_as_email' => true],
                        ['deep_string' => 'Hello World', 'deep_validate_as_email' => false],
                        ['deep_string' => 'hello@world.test', 'deep_validate_as_email' => true],
                    ],
                ],
            ],
        ], [
            'collection.0.items.0.deep_string' => [__('validation.email', ['attribute' => 'collection.0.items.0.deep string'])],
            'collection.1.string' => [__('validation.email', ['attribute' => 'collection.1.string'])],
            'collection.1.items.0.deep_string' => [__('validation.email', ['attribute' => 'collection.1.items.0.deep string'])],
        ]);
});

it('can nest data using relative rule generation', function () {
    $dataClass = new class () extends Data {
        public DummyDataWithContextOverwrittenValidationRules $nested;
    };

    $payload = [
        'nested' => [
            'string' => 'Hello World',
            'validate_as_email' => true,
        ],
    ];

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'nested' => ['required', 'array'],
            'nested.string' => ['required', 'string', 'email'],
            'nested.validate_as_email' => ['boolean', 'required'],
        ], $payload)
        ->assertErrors($payload);
});

it('correctly_injects_context_in_the_rules_method', function () {
    class NestedClassJ extends Data
    {
        public string $property;

        public static function rules(ValidationContext $context): array
        {
            $correct = $context->payload['property'] === 'J'
                && $context->fullPayload['property'] === 'Root'
                && $context->path->equals('nested.data');

            if (! $correct) {
                throw new Exception('Should not end up here');
            }

            return [];
        }
    }

    class NestedClassK extends Data
    {
        public string $property;

        public static function rules(ValidationContext $context): array
        {
            $correct = $context->payload['property'] === 'K'
                && $context->fullPayload['property'] === 'Root'
                && $context->path->equals('nested.collection.0.nested');

            if (! $correct) {
                throw new Exception('Should not end up here');
            }

            return [];
        }
    }


    class NestedClassL extends Data
    {
        public string $property;

        public static function rules(ValidationContext $context): array
        {
            $correct = $context->payload['property'] === 'L'
                && $context->fullPayload['property'] === 'Root'
                && $context->path->equals('nested.collection.0.collection.0');

            if (! $correct) {
                throw new Exception('Should not end up here');
            }

            return [];
        }
    }

    class NestedClassM extends Data
    {
        public string $property;

        public NestedClassK $nested;

        #[DataCollectionOf(NestedClassL::class)]
        public DataCollection $collection;

        public static function rules(ValidationContext $context): array
        {
            $correct = $context->payload['property'] === 'M'
                && $context->fullPayload['property'] === 'Root'
                && $context->path->equals('nested.collection.0');

            if (! $correct) {
                throw new Exception('Should not end up here');
            }

            return [];
        }
    }

    class NestedClassN extends Data
    {
        public string $property;

        public NestedClassJ $data;

        #[DataCollectionOf(NestedClassM::class)]
        public DataCollection $collection;

        public static function rules(ValidationContext $context): array
        {
            $correct = $context->payload['property'] === 'N'
                && $context->fullPayload['property'] === 'Root'
                && $context->path->equals('nested');

            if (! $correct) {
                throw new Exception('Should not end up here');
            }

            return [];
        }
    }

    $dataClass = new class () extends Data {
        public string $property;

        public NestedClassN $nested;

        public static function rules(ValidationContext $context): array
        {
            $correct = $context->payload['property'] === 'Root'
                && $context->fullPayload['property'] === 'Root'
                && $context->path->isRoot();

            if (! $correct) {
                throw new Exception('Should not end up here');
            }

            return [];
        }
    };

    $payload = [
        'property' => 'Root',
        'nested' => [
            'property' => 'N',
            'data' => [
                'property' => 'J',
            ],
            'collection' => [
                [
                    'property' => 'M',
                    'nested' => [
                        'property' => 'K',
                    ],
                    'collection' => [
                        [
                            'property' => 'L',
                        ],
                    ],
                ],
            ],
        ],
    ];

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'property' => ['required', 'string'],
            'nested' => ['required', 'array'],
            'nested.property' => ['required', 'string'],
            'nested.data' => ['required', 'array'],
            'nested.data.property' => ['required', 'string'],
            'nested.collection' => ['present', 'array'],
            'nested.collection.0.property' => ['required', 'string'],
            'nested.collection.0.nested' => ['required', 'array'],
            'nested.collection.0.nested.property' => ['required', 'string'],
            'nested.collection.0.collection' => ['present', 'array'],
            'nested.collection.0.collection.0.property' => ['required', 'string'],
        ], $payload);
});

it('will merge overwritten rules on inherited data objects', function () {
    $data = new class () extends Data {
        public SimpleDataWithOverwrittenRules $nested;

        /** @var DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleDataWithOverwrittenRules> */
        public DataCollection $collection;
    };

    $payload = [
        'nested' => ['string' => 'test'],
        'collection' => [
            ['string' => 'test'],
        ],
    ];

    DataValidationAsserter::for($data)->assertRules([
        'nested' => ['required', 'array'],
        'nested.string' => ['string', 'required', 'min:10', 'max:100'],
        'collection' => ['present', 'array'],
        'collection.0.string' => ['string', 'required', 'min:10', 'max:100'],
    ], $payload)->assertErrors($payload);
});

it('will reduce attribute rules to Laravel rules in the end', function () {
    $dataClass = new class () extends Data {
        public int $property;

        public static function rules(): array
        {
            return [
                'property' => [
                    new IntegerType(),
                    new Exists('table', where: fn (Builder $builder) => $builder->is_admin),
                ],
            ];
        }
    };

    DataValidationAsserter::for($dataClass)->assertRules([
        'property' => [
            'integer',
            (new LaravelExists('table'))->where(fn (Builder $builder) => $builder->is_admin),
        ],
    ]);
});

it('can reference route parameters as values within rules', function () {
    $dataClass = new class () extends Data {
        #[Unique('posts', ignore: new RouteParameterReference('post_id'))]
        public int $property;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('post_id')->andReturns('69');
    $this->app->bind('request', fn () => $requestMock);

    DataValidationAsserter::for($dataClass)->assertRules([
        'property' => [
            'required',
            'numeric',
            'unique:posts,NULL,"69",id',
        ],
    ]);
});

it('can reference route models with a property as values within rules', function () {
    $dataClass = new class () extends Data {
        #[Unique('posts', ignore: new RouteParameterReference('post', 'id'))]
        public int $property;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('post')->andReturns(new DummyModel([
        'id' => 69,
    ]));
    $this->app->bind('request', fn () => $requestMock);

    DataValidationAsserter::for($dataClass)->assertRules([
        'property' => [
            'required',
            'numeric',
            'unique:posts,NULL,"69",id',
        ],
    ]);
});

it('can reference the current logged in user as values within rules', function () {
    actingAs($user = new FakeAuthenticatable());

    $dataClass = new class () extends Data {
        #[Unique('users', ignore: new AuthenticatedUserReference())]
        public int $property;
    };

    expect($dataClass::getValidationRules([]))->toEqual([
        'property' => [
            'required',
            'numeric',
            Rule::unique('users')->ignore($user),
        ],
    ]);
});

it('can reference a container dependency as values within rules', function () {
    app()->bind('max-allowed-size', fn () => 100);

    $dataClass = new class () extends Data {
        #[Max(value: new ContainerReference('max-allowed-size'))]
        public int $property;
    };

    DataValidationAsserter::for($dataClass)->assertRules([
        'property' => [
            'required',
            'numeric',
            'max:100',
        ],
    ]);
});

it('can set the validator to stop on the first failure', function () {
    $dataClass = new class () extends Data {
        #[Min(10)]
        public int $propertyA;

        #[Min(10)]
        public int $propertyB;

        public static function stopOnFirstFailure(): bool
        {
            return true;
        }
    };

    DataValidationAsserter::for($dataClass)->assertRules([
        'propertyA' => ['required', 'numeric', 'min:10'],
        'propertyB' => ['required', 'numeric', 'min:10'],
    ])->assertErrors([
        'propertyA' => 0,
        'propertyB' => 0,
    ], ['propertyA' => [__('validation.min.numeric', ['attribute' => 'property a', 'min' => '10']),]]);
});

it('can manually set validation messages', function () {
    $data = new class () extends Data {
        public string $name;

        public string $song;

        public static function messages(): array
        {
            return [
                'name.required' => 'Fix it Rick!',
            ];
        }
    };

    DataValidationAsserter::for($data)
        ->assertMessages(
            messages: ['name.required' => 'Fix it Rick!'],
            payload: ['song' => 'Never Gonna Give You Up'],
        )
        ->assertErrors(
            payload: ['song' => 'Never Gonna Give You Up'],
            errors: ['name' => ['Fix it Rick!']]
        );

    $data = new class () extends Data {
        public string $name;

        public string $song;

        public static function messages(): array
        {
            return [
                'name' => ['required' => 'Fix it Rick!'],
            ];
        }
    };

    DataValidationAsserter::for($data)
        ->assertMessages(
            messages: ['name' => ['required' => 'Fix it Rick!']],
            payload: ['song' => 'Never Gonna Give You Up'],
        )
        ->assertErrors(
            payload: ['song' => 'Never Gonna Give You Up'],
            errors: ['name' => ['Fix it Rick!']]
        );

    $data = new class () extends Data {
        public string $name;

        public string $song;

        public static function messages(): array
        {
            return [
                'required' => 'Fix it Rick!',
            ];
        }
    };

    DataValidationAsserter::for($data)
        ->assertMessages(
            messages: ['*.required' => 'Fix it Rick!'],
            payload: [],
        )
        ->assertErrors(
            payload: [],
            errors: ['name' => ['Fix it Rick!'], 'song' => ['Fix it Rick!']]
        );
});

it('can manually set messages nested', function () {
    class TestNestedValidationMessagesDataA extends Data
    {
        public string $name;

        public string $song;

        public static function messages(): array
        {
            return [
                'name.required' => 'Fix it Rick!',
            ];
        }
    }

    DataValidationAsserter::for(new class () extends Data {
        public TestNestedValidationMessagesDataA $nested;
    })
        ->assertMessages(
            messages: ['nested.name.required' => 'Fix it Rick!'],
            payload: ['nested' => ['song' => 'Never Gonna Give You Up']],
        )
        ->assertErrors(
            payload: ['nested' => ['song' => 'Never Gonna Give You Up']],
            errors: ['nested.name' => ['Fix it Rick!']]
        );

    class TestNestedValidationMessagesDataB extends Data
    {
        public string $name;

        public string $song;

        public static function messages(): array
        {
            return [
                'name' => ['required' => 'Fix it Rick!'],
            ];
        }
    }

    DataValidationAsserter::for(new class () extends Data {
        public TestNestedValidationMessagesDataB $nested;
    })
        ->assertMessages(
            messages: ['nested.name' => ['required' => 'Fix it Rick!']],
            payload: ['nested' => ['song' => 'Never Gonna Give You Up']],
        )
        ->assertErrors(
            payload: ['nested' => ['song' => 'Never Gonna Give You Up']],
            errors: ['nested.name' => ['Fix it Rick!']]
        );

    class TestNestedValidationMessagesDataC extends Data
    {
        public string $name;

        public string $song;

        public static function messages(): array
        {
            return [
                'required' => 'Fix it Rick!',
            ];
        }
    }

    DataValidationAsserter::for(new class () extends Data {
        public TestNestedValidationMessagesDataC $nested;
    })
        ->assertMessages(
            messages: ['nested.*.required' => 'Fix it Rick!'],
            payload: ['nested' => []],
        )
        ->assertErrors(
            payload: ['nested' => []],
            errors: [
                'nested' => [__('validation.required', ['attribute' => 'nested'])],
                'nested.name' => ['Fix it Rick!'],
                'nested.song' => ['Fix it Rick!'],
            ]
        );

    DataValidationAsserter::for(new class () extends Data {
        public TestNestedValidationMessagesDataC $nested;

        public static function messages(...$args): array
        {
            return [
                'required' => 'Fix it Rick root!',
            ];
        }
    })
        ->assertMessages(
            messages: [
                '*.required' => 'Fix it Rick root!',
                'nested.*.required' => 'Fix it Rick!',
            ],
            payload: ['nested' => []],
        )
        ->assertErrors(
            payload: ['nested' => []],
            errors: [
                'nested' => ['Fix it Rick root!'],
                'nested.name' => ['Fix it Rick!'],
                'nested.song' => ['Fix it Rick!'],
            ]
        );
});

it('can manually set messages in collections', function () {
    class TestCollectionValidationMessagesDataA extends Data
    {
        public string $name;

        public string $song;

        public static function messages(): array
        {
            return [
                'name.required' => 'Fix it Rick!',
            ];
        }
    }

    DataValidationAsserter::for(new class () extends Data {
        #[DataCollectionOf(TestCollectionValidationMessagesDataA::class)]
        public DataCollection $collection;
    })
        ->assertMessages(
            messages: ['collection.*.name.required' => 'Fix it Rick!'],
            payload: ['collection' => [['song' => 'Never Gonna Give You Up']]],
        )
        ->assertErrors(
            payload: [
                'collection' => [
                    ['song' => 'Never Gonna Give You Up'],
                    ['song' => 'Together Forever'],
                ],
            ],
            errors: [
                'collection.0.name' => ['Fix it Rick!'],
                'collection.1.name' => ['Fix it Rick!'],
            ]
        );

    class TestCollectionValidationMessagesDataB extends Data
    {
        public string $name;

        public string $song;

        public static function messages(): array
        {
            return [
                'name' => ['required' => 'Fix it Rick!'],
            ];
        }
    }

    DataValidationAsserter::for(new class () extends Data {
        #[DataCollectionOf(TestCollectionValidationMessagesDataB::class)]
        public DataCollection $collection;
    })
        ->assertMessages(
            messages: ['collection.*.name' => ['required' => 'Fix it Rick!']],
            payload: ['collection' => [['song' => 'Never Gonna Give You Up']]],
        )
        ->assertErrors(
            payload: [
                'collection' => [
                    ['song' => 'Never Gonna Give You Up'],
                    ['song' => 'Together Forever'],
                ],
            ],
            errors: [
                'collection.0.name' => ['Fix it Rick!'],
                'collection.1.name' => ['Fix it Rick!'],
            ]
        );

    class TestCollectionValidationMessagesDataC extends Data
    {
        public string $name;

        public string $song;

        public static function messages(): array
        {
            return [
                'required' => 'Fix it Rick!',
            ];
        }
    }

    DataValidationAsserter::for(new class () extends Data {
        #[DataCollectionOf(TestCollectionValidationMessagesDataC::class)]
        public DataCollection $collection;
    })
        ->assertMessages(
            messages: ['collection.*.*.required' => 'Fix it Rick!'],
            payload: ['collection' => [['song' => 'Never Gonna Give You Up']]],
        )
        ->assertErrors(
            payload: [
                'collection' => [
                    ['song' => 'Never Gonna Give You Up'],
                    ['song' => 'Together Forever'],
                ],
            ],
            errors: [
                'collection.0.name' => ['Fix it Rick!'],
                'collection.1.name' => ['Fix it Rick!'],
            ]
        );
});

it('can manually set messages in double nested collections (yeah this failed once)', function () {
    class TestDoubleNestedCollectionValidationMessagesDataA extends Data
    {
        public string $name;

        public string $song;

        public static function messages(): array
        {
            return [
                'name.required' => 'Fix it Rick!',
            ];
        }
    }

    class TestDoubleNestedCollectionValidationMessagesInitialDataA extends Data
    {
        #[DataCollectionOf(TestDoubleNestedCollectionValidationMessagesDataA::class)]
        public DataCollection $nestedCollection;
    }

    DataValidationAsserter::for(new class () extends Data {
        #[DataCollectionOf(TestDoubleNestedCollectionValidationMessagesInitialDataA::class)]
        public DataCollection $collection;
    })
        ->assertMessages(
            messages: ['collection.*.nestedCollection.*.name.required' => 'Fix it Rick!'],
            payload: ['collection' => [['nestedCollection' => ['collection' => [['song' => 'Never Gonna Give You Up']]]]]],
        )
        ->assertErrors(
            payload: [
                'collection' => [
                    [
                        'nestedCollection' => [
                            ['song' => 'Never Gonna Give You Up'],
                            ['song' => 'Giving up on love'],
                        ],
                    ],
                    ['nestedCollection' => [['song' => 'Together Forever']]],
                ],
            ],
            errors: [
                'collection.0.nestedCollection.0.name' => ['Fix it Rick!'],
                'collection.0.nestedCollection.1.name' => ['Fix it Rick!'],
                'collection.1.nestedCollection.0.name' => ['Fix it Rick!'],
            ]
        );
});

it('can resolve validation dependencies for messages', function () {
    FakeInjectable::setup('Rick Astley');

    $data = new class () extends Data {
        public string $name;

        public static function messages(FakeInjectable $injectable): array
        {
            return [
                'name.required' => $injectable->value === 'Rick Astley' ? 'Fix it Rick!' : 'Fix it!',
            ];
        }
    };

    DataValidationAsserter::for($data)->assertErrors(
        payload: ['name' => null],
        errors: ['name' => ['Fix it Rick!']]
    );
});

it('can manually set validation attributes ', function () {
    $data = new class () extends Data {
        public string $name;

        public static function attributes(): array
        {
            return [
                'name' => 'rickster',
            ];
        }
    };

    DataValidationAsserter::for($data)
        ->assertAttributes([
            'name' => 'rickster',
        ])
        ->assertErrors(
            payload: ['name' => null],
            errors: ['name' => [__('validation.required', ['attribute' => 'rickster'])]]
        );
});

it('can manually set nested validation attributes ', function () {
    class TestNestedValidationAttributesData extends Data
    {
        public string $name;

        public static function attributes(): array
        {
            return [
                'name' => 'rickster',
            ];
        }
    }

    $data = new class () extends Data {
        public TestNestedValidationAttributesData $nested;
    };

    DataValidationAsserter::for($data)
        ->assertAttributes(
            ['nested.name' => 'rickster'],
            payload: ['nested' => ['name' => null]]
        )
        ->assertErrors(
            payload: ['nested' => ['name' => null]],
            errors: ['nested.name' => [__('validation.required', ['attribute' => 'rickster'])]]
        );
});

it('can manually set collected validation attributes ', function () {
    class TestCollectedValidationAttributesData extends Data
    {
        public string $name;

        public static function attributes(): array
        {
            return [
                'name' => 'rickster',
            ];
        }
    }

    $data = new class () extends Data {
        #[DataCollectionOf(TestCollectedValidationAttributesData::class)]
        public DataCollection $collection;
    };

    DataValidationAsserter::for($data)
        ->assertAttributes(
            ['collection.*.name' => 'rickster'],
            payload: ['collection' => [['name' => null]]],
        )
        ->assertErrors(
            payload: ['collection' => [['name' => null]]],
            errors: ['collection.0.name' => [__('validation.required', ['attribute' => 'rickster'])]]
        );
})->skip('Feature not supported by Laravel at the moment');

it('can resolve validation dependencies for attributes ', function () {
    FakeInjectable::setup('Rick Astley');

    $data = new class () extends Data {
        public string $name;

        public static function attributes(FakeInjectable $injectable): array
        {
            return [
                'name' => $injectable->value === 'Rick Astley' ? 'rickster' : 'someone',
            ];
        }
    };

    DataValidationAsserter::for($data)->assertErrors(
        payload: ['name' => null],
        errors: ['name' => [__('validation.required', ['attribute' => 'rickster'])]]
    );
});

it('can manually set the redirect url', function () {
    $data = new class () extends Data {
        public string $name;

        public static function redirect(): string
        {
            return '/never-given-up';
        }
    };

    DataValidationAsserter::for($data)->assertRedirect(
        payload: ['name' => null],
        redirect: '/never-given-up'
    );
});

it('can resolve validation dependencies for redirect url', function () {
    FakeInjectable::setup('Rick Astley');

    $data = new class () extends Data {
        public string $name;

        public static function redirect(FakeInjectable $injectable): string
        {
            return $injectable->value === 'Rick Astley' ? '/never-given-up' : '/given-up';
        }
    };

    DataValidationAsserter::for($data)->assertRedirect(
        payload: ['name' => null],
        redirect: '/never-given-up'
    );
});

it('can manually set the redirect route', function () {
    Route::get('/never-given-up', fn () => 'Never gonna give you up')->name('never-given-up');

    $data = new class () extends Data {
        public string $name;

        public static function redirectRoute(): string
        {
            return 'never-given-up';
        }
    };

    DataValidationAsserter::for($data)->assertRedirect(
        payload: ['name' => null],
        redirect: 'http://localhost/never-given-up'
    );
});

it('can resolve validation dependencies for redirect route', function () {
    FakeInjectable::setup('Rick Astley');

    Route::get('/never-given-up', fn () => 'Never gonna give you up')->name('never-given-up');

    $data = new class () extends Data {
        public string $name;

        public static function redirectRoute(FakeInjectable $injectable): string
        {
            return $injectable->value === 'Rick Astley' ? 'never-given-up' : 'given-up';
        }
    };

    DataValidationAsserter::for($data)->assertRedirect(
        payload: ['name' => null],
        redirect: 'http://localhost/never-given-up'
    );
});

it('can manually specify the validator', function () {
    $dataClass = new class () extends Data {
        public string $property;

        public static function withValidator(Validator $validator): void
        {
            $validator->setRules([]);
        }
    };

    DataValidationAsserter::for($dataClass)->assertOk([]);
});

it('can resolve validation dependencies for error bag', function () {
    FakeInjectable::setup('Rick Astley');

    $data = new class () extends Data {
        public string $name;

        public static function errorBag(FakeInjectable $injectable): string
        {
            return $injectable->value === 'Rick Astley' ? 'never-given-up' : 'given-up';
        }
    };

    DataValidationAsserter::for($data)->assertErrorBag(
        payload: ['name' => null],
        errorBag: 'never-given-up'
    );
});

it('can validate a payload for a data object without creating one', function () {
    expect(SimpleData::validate(['string' => 'Hello World']))->toMatchArray([
        'string' => 'Hello World',
    ]);

    try {
        SimpleData::validate(['string' => 10]);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'string' => [__('validation.string', ['attribute' => 'string'])],
        ]);

        return;
    }

    assertFalse(true, 'We should not end up here');
});

it('can validate a payload for a data object and create one', function () {
    $data = SimpleData::validateAndCreate(['string' => 'Hello World']);

    expect($data->string)->toEqual('Hello World');

    try {
        SimpleData::validateAndCreate(['string' => 10]);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'string' => [__('validation.string', ['attribute' => 'string'])],
        ]);

        return;
    }

    assertFalse(true, 'We should not end up here');
});

it('can validate a payload for a data object and create one using a magic from method', function () {
    $dataClass = new class () extends Data {
        public string $string;

        public static function fromRequest(Request $request): self
        {
            $self = new self();

            $self->string = strtoupper($request->input('string'));

            return $self;
        }
    };

    $data = $dataClass::validateAndCreate(
        (new Request())->merge(['string' => 'hello world']),
    );

    expect($data)
        ->string->toBe('HELLO WORLD')
        ->string->not()->toBe('hello world');

    try {
        SimpleData::validateAndCreate(new Request());
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'string' => [__('validation.required', ['attribute' => 'string'])],
        ]);

        return;
    }

    assertFalse(true, 'We should not end up here');
});

it('can the validation rules for a data object', function () {
    expect(MultiData::getValidationRules([]))->toEqual([
        'first' => ['required', 'string'],
        'second' => ['required', 'string'],
    ]);

    expect(NestedNullableData::getValidationRules(payload: []))->toEqual(['nested' => ['nullable', 'array']]);

    expect(NestedNullableData::getValidationRules(payload: ['nested' => []]))->toEqual([
        'nested' => ['nullable', 'array'],
        'nested.string' => ['required', 'string'],
    ]);
});


it('can apply custom rules onto array properties', function () {
    $dataClass = new class () extends Data {
        #[Min(1)]
        #[Max(5)]
        public readonly array $emails;

        public static function rules(): array
        {
            return [
                'emails.*' => ['email'],
            ];
        }
    };

    expect($dataClass::getValidationRules([]))->toEqual([
        'emails' => ['required', 'array', 'min:1', 'max:5'],
        'emails.*' => ['email'],
    ]);
});

it('can validate data with circular dependencies', function () {
    DataValidationAsserter::for(CircData::class)
        ->assertRules([
            'string' => ['required', 'string'],
            'ular' => ['nullable', 'array'],
        ]);

    DataValidationAsserter::for(CircData::class)
        ->assertOk([
            'string' => 'Hello World',
            'ular' => [
                'string' => 'Hello World',
                'circ' => [
                    'string' => 'Hello World',
                ],
            ],
        ])
        ->assertRules([
            'string' => ['required', 'string'],
            'ular' => ['nullable', 'array'],
            'ular.string' => ['required', 'string'],
            'ular.circ' => ['nullable', 'array'],
            'ular.circ.string' => ['required', 'string'],
            'ular.circ.ular' => ['nullable', 'array'],
        ], payload: [
            'string' => 'Hello World',
            'ular' => [
                'string' => 'Hello World',
                'circ' => [
                    'string' => 'Hello World',
                ],
            ],
        ]);
});

it('can validate a property with custom rules as array containing regex rule with "|"', function () {
    $dataClass = new class () extends Data {
        public string $property;

        public static function rules(): array
        {
            return [
                'property' => ['string', 'required', 'regex:/test|ok/'],
            ];
        }
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'property' => ['string', 'required', 'regex:/test|ok/'],
        ])
        ->assertOk([
            'property' => 'ok',
        ]);
});

it('can handle a string as (wrong) payload', function () {
    DataValidationAsserter::for(NestedData::class)
        ->assertErrors(['hello world'])
        ->assertErrors([
            'simple' => 'hello-world',
        ]);
});

it('can use laravel-data validation rules in laravel validator', function () {
    $rules = [new Required(), new StringType(), new Max(10)];

    $validatorToPass = ValidatorFacade::make(
        [
            'property' => 'test',
        ],
        [
            'property' => $rules,
        ],
    );

    $validatorToFail = ValidatorFacade::make(
        [
            'property' => 'testLongerText',
        ],
        [
            'property' => $rules,
        ],
    );

    expect($validatorToPass->passes())->toBeTrue()
        ->and($validatorToFail->passes())->toBeFalse();
});

it('wont validate default values when they are not provided', function () {
    $dataClass = new class () extends Data {
        #[Min(10)]
        public string $default = 'Hello World';
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([])
        ->assertOk(['default' => 'Hi there in this world'])
        ->assertErrors(['default' => 'minimal'])
        ->assertErrors(['default' => null])
        ->assertRules([], payload: [])
        ->assertRules([
            'default' => ['required', 'string', 'min:10'],
        ], ['default' => 'something']);
});

it('wont validate default values when they are not provided and rules are overwritten', function () {
    $dataClass = new class () extends Data {
        public string $default = 'Hello World';

        public static function rules(ValidationContext $context): array
        {
            return [
                'default' => ['required', 'string', 'min:10'],
            ];
        }
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([])
        ->assertOk(['default' => 'Hi there in this world'])
        ->assertErrors(['default' => 'minimal'])
        ->assertErrors(['default' => null])
        ->assertRules([], payload: [])
        ->assertRules([
            'default' => ['required', 'string', 'min:10'],
        ], ['default' => 'something']);
});

it('will ignore default values which are optional', function () {
    $dataClass = new class () extends Data {
        public function __construct(public string|Optional $property = new Optional())
        {
        }
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk([])
        ->assertOk(['property' => 'Hello World'])
        ->assertErrors(['property' => 123])
        ->assertErrors(['property' => null])
        ->assertRules(['property' => ['sometimes', 'string']]);
});

it('a manual written present attribute rule always overwrites a generated required rule', function () {
    $dataClass = new class () extends Data {
        #[Present]
        public array $array;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['array' => []])
        ->assertOk(['array' => ['a', 'b']])
        ->assertErrors(['array' => null])
        ->assertRules([
            'array' => ['array', 'present'],
        ], []);
});

it('supports custom validation attributes', function () {
    $dataClass = new class () extends Data {
        #[PassThroughCustomValidationAttribute(['url'])]
        public string $url;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['url' => 'https://spatie.be'])
        ->assertErrors(['url' => 'nowp'])
        ->assertRules([
            'url' => ['required', 'string', 'url'],
        ], []);

    $dataClass = new class () extends Data {
        #[PassThroughCustomValidationAttribute(['url', 'max:20'])]
        public string $url;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['url' => 'https://spatie.be'])
        ->assertErrors(['url' => 'nowp'])
        ->assertErrors(['url' => 'https://rubenvanassche.com'])
        ->assertRules([
            'url' => ['required', 'string', 'url', 'max:20'],
        ], []);

    $dataClass = new class () extends Data {
        #[PassThroughCustomValidationAttribute([new LaravelIn(['a', 'b'])])]
        public string $something;
    };


    DataValidationAsserter::for($dataClass)
        ->assertOk(['something' => 'a'])
        ->assertOk(['something' => 'b'])
        ->assertErrors(['something' => 'c'])
        ->assertRules([
            'something' => ['required', 'string', new LaravelIn(['a', 'b'])],
        ], []);
});

it('can add a requiring rule on an attribute which will overwrite the optional type', function () {
    $dataClass = new class () extends Data {
        #[Required]
        #[BooleanType]
        public bool $success;

        #[RequiredIf('success', 'false')]
        #[StringType]
        public string $error = '';

        #[RequiredIf('success', 'true')]
        #[IntegerType]
        public Optional|int $id;
    };

    DataValidationAsserter::for($dataClass)
        ->assertOk(['success' => true, 'id' => 1])
        ->assertErrors(['success' => true]);
})->skip('V5: The rule inferrers need to be rewritten/removed for this, we need to first add attribute rules and then decide require stuff');

it('can validate an optional but nonexists attribute', function () {
    $dataClass = new class () extends Data {
        public array|null|Optional $property;
    };

    expect($dataClass::from()->toArray())->toBe([]);
    expect($dataClass::from([])->toArray())->toBe([]);
    expect($dataClass::from(['property' => null])->toArray())->toBe(['property' => null]);
    expect($dataClass::from(['property' => []])->toArray())->toBe(['property' => []]);
    expect($dataClass::validateAndCreate([])->toArray())->toBe([]);
});

it('is possible to define the validation strategy for each data object globally using config', function () {
    $dataClass = new class () extends Data {
        #[In('Hello World')]
        public string $string;
    };

    expect($dataClass::from(['string' => 'Nowp']))
        ->toBeInstanceOf(Data::class)
        ->string->toBe('Nowp');

    config()->set('data.validation_strategy', ValidationStrategy::Always->value);

    expect(fn () => $dataClass::from(['string' => 'Nowp']))
        ->toThrow(ValidationException::class);
});

it('handles validation with mapped attributes', function () {
    #[MapInputName(SnakeCaseMapper::class)]
    class TestValidationWithClassMappedAttribute extends Data
    {
        public function __construct(
            #[Required]
            public readonly int $someProperty,
        ) {
        }
    }

    // Problem:
    // some_property is mapped onto someProperty
    // We generate rules for some_property -> we always generate rules for the mapped attribute if present
    // So validation fails

    $data = TestValidationWithClassMappedAttribute::factory()->alwaysValidate()->from([
        'some_property' => 1,
    ]);
})->skip('Validation problem, fix in v5');

it('will remove a sometimes rule generated by an Optional type when manually requiring something', function () {
    $dataClass = new class () extends Data {
        #[RequiredWith('otherProperty')]
        public string|Optional $property;

        public string|null $otherProperty;
    };

    DataValidationAsserter::for($dataClass)
        ->assertRules([
            'property' => ['string', 'required_with:otherProperty'],
            'otherProperty' => ['nullable', 'string'],
        ], ['property' => 'Hello World', 'otherProperty' => 'Hello World'])
        ->assertOk([
            'property' => 'Hello World',
            'otherProperty' => 'Hello World',
        ])
        ->assertOk([
            'otherProperty' => null,
        ])
        ->assertErrors([
            'otherProperty' => 'Hello World',
        ]);
});

it('it will merge validation rules', function () {
    #[MergeValidationRules]
    class TestNestedDataWithMergedRules extends SimpleData
    {
        public static function rules(ValidationContext $context): array
        {
            return [
                'string' => ['max:10', 'min:2'],
            ];
        }
    }

    #[MergeValidationRules]
    class TestDataWithMergedRuleset extends Data
    {
        public function __construct(
            #[Max(10)]
            public string $array_rules,
            #[Max(10)]
            public string $string_rules,
            #[WithoutValidation]
            public string $without_validation,
            public TestNestedDataWithMergedRules $nested
        ) {
        }

        public static function rules(): array
        {
            return [
                'array_rules' => ['min:2', 'alpha'],
                'string_rules' => 'min:2|alpha',
            ];
        }
    }

    DataValidationAsserter::for(TestDataWithMergedRuleset::class)
        ->assertRules([
            'array_rules' => ['required', 'string', 'max:10', 'min:2', 'alpha'],
            'string_rules' => ['required', 'string', 'max:10', 'min:2', 'alpha'],
            'nested' => ['required', 'array'],
            'nested.string' => ['required', 'string', 'max:10', 'min:2'],
        ], [])
        ->assertOk([
            'array_rules' => 'Ruben',
            'string_rules' => 'Ruben',
            'nested' => ['string' => 'Ruben'],
        ])
        ->assertErrors([
            'array_rules' => 'r',
            'string_rules' => 'r',
            'nested' => ['string' => 'r'],
        ], [
            'array_rules' => [__('validation.min.string', ['attribute' => 'array rules', 'min' => 2])],
            'string_rules' => [__('validation.min.string', ['attribute' => 'string rules', 'min' => 2])],
            'nested.string' => [__('validation.min.string', ['attribute' => 'nested.string', 'min' => 2])],
        ])
        ->assertErrors([
            'array_rules' => 'rubenvanassche',
            'string_rules' => 'rubenvanassche',
            'nested' => ['string' => 'rubenvanassche'],
        ], [
            'array_rules' => [__('validation.max.string', ['attribute' => 'array rules', 'max' => 10])],
            'string_rules' => [__('validation.max.string', ['attribute' => 'string rules', 'max' => 10])],
            'nested.string' => [__('validation.max.string', ['attribute' => 'nested.string', 'max' => 10])],
        ])
        ->assertErrors([
            'array_rules' => null,
            'string_rules' => null,
            'nested' => ['string' => null],
        ], [
            'array_rules' => [__('validation.required', ['attribute' => 'array rules'])],
            'string_rules' => [__('validation.required', ['attribute' => 'string rules'])],
            'nested.string' => [__('validation.required', ['attribute' => 'nested.string'])],
        ]);
});

describe('property-morphable validation tests', function () {
    enum TestValidationPropertyMorphableEnum: string
    {
        case A = 'a';
        case B = 'b';
    }

    ;

    abstract class TestValidationAbstractPropertyMorphableData extends Data implements PropertyMorphableData
    {
        public function __construct(
            #[PropertyForMorph]
            public TestValidationPropertyMorphableEnum $variant,
        ) {
        }

        public static function morph(array $properties): ?string
        {
            return match ($properties['variant']) {
                TestValidationPropertyMorphableEnum::A => TestValidationPropertyMorphableDataA::class,
                TestValidationPropertyMorphableEnum::B => TestValidationPropertyMorphableDataB::class,
                default => null,
            };
        }
    }

    class TestValidationPropertyMorphableDataA extends TestValidationAbstractPropertyMorphableData
    {
        public function __construct(public string $a, public DummyBackedEnum $enum)
        {
            parent::__construct(TestValidationPropertyMorphableEnum::A);
        }
    }

    class TestValidationPropertyMorphableDataB extends TestValidationAbstractPropertyMorphableData
    {
        public function __construct(public string $b)
        {
            parent::__construct(TestValidationPropertyMorphableEnum::B);
        }
    }

    it('can validate property-morphable data', function () {
        DataValidationAsserter::for(TestValidationAbstractPropertyMorphableData::class)
            ->assertErrors([], [
                'variant' => ['The variant field is required.'],
            ])
            ->assertErrors([
                'variant' => 'c',
            ], [
                'variant' => [
                    'The selected variant is invalid.',
                    'The selected variant is invalid for morph.',
                ],
            ])
            ->assertErrors([
                'variant' => 'a',
            ], [
                'a' => ['The a field is required.'],
                'enum' => ['The enum field is required.'],
            ])
            ->assertErrors([
                'variant' => 'a',
                'a' => 'foo',
                'enum' => 'invalid',
            ], [
                'enum' => ['The selected enum is invalid.'],
            ])
            ->assertErrors([
                'variant' => 'b',
            ], [
                'b' => ['The b field is required.'],
            ])
            ->assertOk([
                'variant' => 'a',
                'a' => 'foo',
                'enum' => 'foo',
            ])
            ->assertOk([
                'variant' => 'b',
                'b' => 'foo',
            ]);
    });

    it('can validate nested property-morphable data', function () {
        class TestValidationNestedPropertyMorphableData extends Data
        {
            public function __construct(
                /** @var TestValidationAbstractPropertyMorphableData[] */
                public ?DataCollection $nestedCollection,
            ) {
            }
        }

        ;

        DataValidationAsserter::for(TestValidationNestedPropertyMorphableData::class)
            ->assertErrors([
                'nestedCollection' => [[]],
            ], [
                'nestedCollection.0.variant' => ['The nested collection.0.variant field is required.'],
            ])
            ->assertErrors([
                'nestedCollection' => [['variant' => 'c']],
            ], [
                'nestedCollection.0.variant' => [
                    'The selected nested collection.0.variant is invalid.',
                    'The selected nested collection.0.variant is invalid for morph.',
                ],
            ])
            ->assertErrors([
                'nestedCollection' => [['variant' => 'a'], ['variant' => 'b']],
            ], [
                'nestedCollection.0.a' => ['The nested collection.0.a field is required.'],
                'nestedCollection.0.enum' => ['The nested collection.0.enum field is required.'],
                'nestedCollection.1.b' => ['The nested collection.1.b field is required.'],
            ])
            ->assertErrors([
                'nestedCollection' => [['variant' => 'a', 'a' => 'foo', 'enum' => 'invalid']],
            ], [
                'nestedCollection.0.enum' => ['The selected nested collection.0.enum is invalid.'],
            ])
            ->assertOk([
                'nestedCollection' => [['variant' => 'a', 'a' => 'foo', 'enum' => 'foo'], ['variant' => 'b', 'b' => 'bar']],
            ]);
    });

    it('can validate property-morphable data with a default', function () {
        abstract class TestValidationAbstractPropertyMorphableDefaultData extends Data implements PropertyMorphableData
        {
            #[\Spatie\LaravelData\Attributes\PropertyForMorph]
            public TestValidationPropertyMorphableEnum $variant = TestValidationPropertyMorphableEnum::A;

            public static function morph(array $properties): ?string
            {
                return match ($properties['variant'] ?? null) {
                    TestValidationPropertyMorphableEnum::A => TestValidationPropertyMorphableDefaultDataA::class,
                    default => null,
                };
            }
        }

        class TestValidationPropertyMorphableDefaultDataA extends TestValidationAbstractPropertyMorphableDefaultData
        {
            public function __construct(public string $a, public DummyBackedEnum $enum)
            {
                $this->variant = TestValidationPropertyMorphableEnum::A;
            }
        }

        DataValidationAsserter::for(TestValidationAbstractPropertyMorphableDefaultData::class)
            ->assertErrors([], [
                'a' => ['The a field is required.'],
                'enum' => ['The enum field is required.'],
            ])
            ->assertOk([
                'a' => 'foo',
                'enum' => 'foo',
            ])
            ->assertOk([
                'variant' => 'a',
                'a' => 'foo',
                'enum' => 'foo',
            ]);
    });
});
