<?php

use Spatie\LaravelData\Casts\BuiltinTypeCast;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;

it('can cast builtin types', function (string $type, mixed $input, mixed $expected) {
    $class = new class () {
        public bool|int|float|string|array $value;
    };

    $caster = new BuiltinTypeCast($type);

    expect(
        $caster->cast(
            FakeDataStructureFactory::property($class, 'value'),
            $input,
            [],
            CreationContextFactory::createFromConfig($class::class)->get()
        )
    )->toBe($expected);
})->with([
    'string "true" to bool' => ['bool', 'true', true],
    'string "false" to bool' => ['bool', 'false', false],
    'string "TRUE" (uppercase) to bool' => ['bool', 'TRUE', true],
    'string "FALSE" (uppercase) to bool' => ['bool', 'FALSE', false],
    'mixed case string "TrUe" to bool' => ['bool', 'TrUe', true],
    'integer 1 to bool true' => ['bool', 1, true],
    'integer 0 to bool false' => ['bool', 0, false],
    'non-empty string to bool true' => ['bool', 'some text', true],
    'empty string to bool false' => ['bool', '', false],
    'string "0" to bool false' => ['bool', '0', false],
    'string "1" to bool true' => ['bool', '1', true],
    'int types' => ['int', '42', 42],
    'float types' => ['float', '42.5', 42.5],
    'string types' => ['string', 42, '42'],
    'array types' => ['array', (object) ['key' => 'value'], ['key' => 'value']],
]);
