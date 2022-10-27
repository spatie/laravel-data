<?php

use function Pest\Laravel\postJson;
use Spatie\LaravelData\Support\Validation\ValidationRule;
use Illuminate\Testing\TestResponse;

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
