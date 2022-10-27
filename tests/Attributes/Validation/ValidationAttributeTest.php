<?php

use Carbon\CarbonImmutable;
use DateTimeZone;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\ValidationAttribute;

it('can get a string representation of rules', function () {
    $rule = new StringType();

    expect((string) $rule)->toEqual('string');
});

it('can normalize values', function ($input, $output) {
    $normalizer = new class() extends ValidationAttribute
    {
        public function execute(mixed $value): mixed
        {
            return $this->normalizeValue($value);
        }

        public function getRules(): array
        {
            return [];
        }

        public static function create(string ...$parameters): static
        {
            return new self();
        }

        public static function keyword(): string
        {
            return '';
        }
    };

    expect($normalizer->execute($input))->toEqual($output);
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
});
