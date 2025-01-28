<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\actingAs;

use Spatie\LaravelData\Attributes\FromAuthenticatedUser;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\FakeAuthenticatable;

it('can get the current logged in user', function () {
    actingAs($user = new FakeAuthenticatable());

    $dataClass = new class () extends Data {
        #[FromAuthenticatedUser]
        public Authenticatable $user;
    };

    expect($dataClass::from()->user)->toBe($user);
});

it('can get the current logged in user using another guard', function () {
    config()->set('auth.guards.other', [
        'driver' => 'session',
        'provider' => 'users',
    ]);

    Auth::guard('other')->setUser($user = new FakeAuthenticatable());

    $dataClass = new class () extends Data {
        #[FromAuthenticatedUser]
        public Authenticatable $user;
    };

    expect(isset($dataClass::from()->user))->toBeFalse();

    $dataClass = new class () extends Data {
        #[FromAuthenticatedUser(guard: 'other')]
        public Authenticatable $user;
    };

    expect($dataClass::from()->user)->toBe($user);
});

it('will not set the user property when not logged in', function () {
    $dataClass = new class () extends Data {
        #[FromAuthenticatedUser]
        public Authenticatable $user;
    };

    expect(isset($dataClass::from()->user))->toBeFalse();
});
