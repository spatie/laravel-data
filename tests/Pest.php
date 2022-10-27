<?php

use function Pest\Laravel\postJson;
use Spatie\LaravelData\Support\Validation\ValidationRule;
use Illuminate\Testing\TestResponse;
use Spatie\LaravelData\Resolvers\DataPropertyValidationRulesResolver;
use Spatie\LaravelData\Resolvers\EmptyDataResolver;
use Spatie\LaravelData\Support\DataProperty;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(Spatie\LaravelData\Tests\TestCase::class)->in('.');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function performRequest(string $string): TestResponse
{
    return postJson('/example-route', [
        'string' => $string,
    ]);
}

function rulesFixture(
    ValidationRule $attribute,
    object|string|array $expected,
    object|string|null $expectCreatedAttribute = null,
    string $exception = null
) {
    return [
        'attribute' => $attribute,
        'expected' => $expected,
        'expectedCreatedAttribute' => $expectCreatedAttribute ?? $attribute,
        'exception' => $exception,
    ];
}

function resolveRules(object $class): array
{
    $reflectionProperty = new ReflectionProperty($class, 'property');

    $property = DataProperty::create($reflectionProperty);

    return app(DataPropertyValidationRulesResolver::class)->execute($property)->toArray();
}

function assertEmptyPropertyValue(
    mixed $expected,
    object $class,
    array $extra = [],
    string $propertyName = 'property',
) {
    $resolver = app(EmptyDataResolver::class);

    $empty = $resolver->execute($class::class, $extra);

    expect($empty)->toHaveKey($propertyName)
        ->and($empty[$propertyName])->toEqual($expected);
}
