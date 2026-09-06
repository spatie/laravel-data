<?php

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Mimes;
use Spatie\LaravelData\Attributes\Validation\MimeTypes;
use Spatie\LaravelData\Attributes\Validation\Prohibits;
use Spatie\LaravelData\Attributes\Validation\RequiredWith;
use Spatie\LaravelData\Attributes\Validation\RequiredWithAll;
use Spatie\LaravelData\Attributes\Validation\RequiredWithout;
use Spatie\LaravelData\Attributes\Validation\RequiredWithoutAll;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Attributes\Validation\Ulid;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;

it('can get a string representation of rules', function () {
    $rule = new StringType();

    expect((string) $rule)->toEqual('string');
});

it('can normalize values', function ($input, $output) {
    $normalizer = new class ([$input]) extends StringValidationAttribute {
        public function __construct(protected array $parameters)
        {
        }

        public static function create(string ...$parameters): static
        {
            return new static(...$parameters);
        }

        public static function keyword(): string
        {
            return 'test';
        }

        public function parameters(): array
        {
            return $this->parameters;
        }
    };

    expect((string) $normalizer)->toEqual("test:{$output}");
})->with(function () {
    yield [
         'Hello world',
        'Hello world',
    ];

    yield [
         42,
        '42',
    ];

    yield [
         3.14,
        '3.14',
    ];

    yield [
         true,
        'true',
    ];

    yield [
         false,
        'false',
    ];

    yield [
         ['a', 'b', 'c'],
        'a,b,c',
    ];

    yield [
         CarbonImmutable::create(2020, 05, 16, 0, 0, 0, new DateTimeZone('Europe/Brussels')),
        '2020-05-16T00:00:00+02:00',
    ];

    yield [
         DummyBackedEnum::FOO,
        'foo',
    ];

    yield [
         [DummyBackedEnum::FOO, DummyBackedEnum::BOO],
        'foo,boo',
    ];
});

it('can use Ulid attribute on constructor promoted parameters', function () {
    $class = new class ('01ARZ3NDEKTSV4RRFFQ69G5FAV') {
        public function __construct(
            #[Ulid]
            public string $id,
        ) {
        }
    };

    expect($class->id)->toBe('01ARZ3NDEKTSV4RRFFQ69G5FAV');
});

it('can initialize validation attributes with default parameters without uninitialized property errors', function () {
    expect((new RequiredWith())->parameters())->toBe([[]])
        ->and((new RequiredWithout())->parameters())->toBe([[]])
        ->and((new RequiredWithAll())->parameters())->toBe([[]])
        ->and((new RequiredWithoutAll())->parameters())->toBe([[]])
        ->and((new Prohibits())->parameters())->toBe([[]])
        ->and((new Url())->parameters())->toBe([])
        ->and((new ArrayType())->parameters())->toBe([])
        ->and((new Mimes())->parameters())->toBe([[]])
        ->and((new MimeTypes())->parameters())->toBe([[]])
        ->and((new Email())->parameters())->toBe(['rfc']);
});
