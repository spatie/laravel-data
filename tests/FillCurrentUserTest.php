<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Spatie\LaravelData\Attributes\FromData\FromCurrentUser;
use Spatie\LaravelData\Attributes\FromData\FromCurrentUserProperty;
use Spatie\LaravelData\Data;
use stdClass;

it('can fill data properties with current user', function () {
    $dataClass = new class () extends Data {
        #[FromCurrentUser]
        public User $user;
    };

    $user = new User();
    $requestMock = mock(Request::class);
    $requestMock->expects('user')->once()->andReturns($user);
    $requestMock->expects('toArray')->once()->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->user)->toBe($user);
});

it('can fill data properties from current user properties', function () {
    $dataClass = new class () extends Data {
        #[FromCurrentUserProperty]
        public int $id;
        #[FromCurrentUserProperty]
        public string $name;
        #[FromCurrentUserProperty]
        public string $email;
    };

    $user = new User();
    $user->id = 123;
    $user->name = 'John Doe';
    $user->email = 'john.doe@example.com';

    $requestMock = mock(Request::class);
    $requestMock->expects('user')->times(3)->andReturns($user);
    $requestMock->expects('toArray')->once()->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->id)->toEqual($user->id);
    expect($data->name)->toEqual($user->name);
    expect($data->email)->toEqual($user->email);
});

it('can fill data properties from specified current user property', function () {
    $dataClass = new class () extends Data {
        #[FromCurrentUserProperty(property: 'id')]
        public int $userId;
        #[FromCurrentUserProperty(property: 'name')]
        public string $userName;
        #[FromCurrentUserProperty(property: 'email')]
        public string $userEmail;
    };

    $user = new User();
    $user->id = 123;
    $user->name = 'Jane Doe';
    $user->email = 'jane.doe@example.com';

    $requestMock = mock(Request::class);
    $requestMock->expects('user')->times(3)->andReturns($user);
    $requestMock->expects('toArray')->once()->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->userId)->toEqual($user->id);
    expect($data->userName)->toEqual($user->name);
    expect($data->userEmail)->toEqual($user->email);
});

it('can fill data properties with current user using specified guard', function () {
    $dataClass = new class () extends Data {
        #[FromCurrentUser('api')]
        public User $user;
    };

    $user = new User();
    $requestMock = mock(Request::class);
    $requestMock->expects('user')->with('api')->once()->andReturns($user);
    $requestMock->expects('toArray')->once()->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->user)->toBe($user);
});

it('replaces properties when current user exists', function () {
    $dataClass = new class () extends Data {
        #[FromCurrentUser]
        public User $user;
    };

    $user = new User();
    $requestMock = mock(Request::class);
    $requestMock->expects('user')->once()->andReturns($user);
    $requestMock->expects('toArray')->once()->andReturns(['user' => new User()]);

    $data = $dataClass::from($requestMock);

    expect($data->user)->toBe($user);
});

it('disables replacing properties when current user exists', function () {
    $dataClass = new class () extends Data {
        #[FromCurrentUser(replaceWhenPresentInBody: false)]
        public User $user;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('user')->never();
    $user = new User();
    $requestMock->expects('toArray')->once()->andReturns(['user' => $user]);

    $data = $dataClass::from($requestMock);

    expect($data->user)->toBe($user);
});

it('fills data properties when replacement is disabled and current user exists with non-existent input property', function () {
    $dataClass = new class () extends Data {
        #[FromCurrentUser(replaceWhenPresentInBody: false)]
        public ?User $user = null;
    };

    $user = new User();
    $requestMock = mock(Request::class);
    $requestMock->expects('user')->once()->andReturns($user);
    $requestMock->expects('toArray')->once()->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->user)->toBe($user);
});

it('can fill data properties with current user using specified userClass', function () {
    $dataClass = new class () extends Data {
        #[FromCurrentUser(userClass: User::class)]
        public User $user;
    };

    $user = new User();
    $requestMock = mock(Request::class);
    $requestMock->expects('user')->once()->andReturns($user);
    $requestMock->expects('toArray')->once()->andReturns([]);
    $data = $dataClass::factory()->withoutValidation()->from($requestMock);
    expect($data->user)->toBeInstanceOf(User::class);
});

it('skips filling data properties when specified userClass does not match', function () {
    $dataClass = new class () extends Data {
        #[FromCurrentUser(userClass: User::class)]
        public ?User $user = null;
    };

    $user = new stdClass();
    $requestMock = mock(Request::class);
    $requestMock->expects('user')->once()->andReturns($user);
    $requestMock->expects('toArray')->once()->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->user)->toBeNull();
});
