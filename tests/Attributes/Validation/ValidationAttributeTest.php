<?php

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Attributes\Validation\ValidationAttribute;
use Spatie\LaravelData\Support\Validation\RulesToLaravel;
use Spatie\LaravelData\Support\Validation\RulesToValidationRule;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Spatie\LaravelData\Tests\Fakes\DummyBackedEnum;

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
        'input' => 'Hello world',
        'output' => 'Hello world',
    ];

    yield [
        'input' => 42,
        'output' => '42',
    ];

    yield [
        'input' => 3.14,
        'output' => '3.14',
    ];

    yield [
        'input' => true,
        'output' => 'true',
    ];

    yield [
        'input' => false,
        'output' => 'false',
    ];

    yield [
        'input' => ['a', 'b', 'c'],
        'output' => 'a,b,c',
    ];

    yield [
        'input' => CarbonImmutable::create(2020, 05, 16, 0, 0, 0, new DateTimeZone('Europe/Brussels')),
        'output' => '2020-05-16T00:00:00+02:00',
    ];

    yield [
        'input' => DummyBackedEnum::FOO,
        'output' => 'foo',
    ];

    yield [
        'input' => [DummyBackedEnum::FOO, DummyBackedEnum::BOO],
        'output' => 'foo,boo',
    ];
});
