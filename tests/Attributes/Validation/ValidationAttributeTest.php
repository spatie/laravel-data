<?php

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
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
