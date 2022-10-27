<?php

use Illuminate\Testing\TestResponse;

use function Pest\Laravel\postJson;

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
