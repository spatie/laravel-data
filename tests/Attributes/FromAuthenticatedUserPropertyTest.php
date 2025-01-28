<?php

namespace Spatie\LaravelData\Tests\Attributes;

use function Pest\Laravel\actingAs;

use Spatie\LaravelData\Attributes\FromAuthenticatedUserProperty;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\FakeAuthenticatable;

it('can get a user property value based upon the property name', function () {
    actingAs($user = new FakeAuthenticatable());

    $dataClass = new class () extends Data {
        #[FromAuthenticatedUserProperty]
        public string $property;
    };

    expect($dataClass::from()->property)->toBe($user->property);
});

it('can get a user property value based upon a key defined in the attribute', function () {
    actingAs($user = new FakeAuthenticatable());

    $dataClass = new class () extends Data {
        #[FromAuthenticatedUserProperty(property: 'property')]
        public string $value;
    };

    expect($dataClass::from()->value)->toBe($user->property);
});
