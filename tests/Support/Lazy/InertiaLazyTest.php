<?php

use Inertia\LazyProp;
use Inertia\OptionalProp;
use Spatie\LaravelData\Support\Lazy\InertiaLazy;

it('resolves to a LazyProp on Inertia v2', function () {
    $lazy = new InertiaLazy(fn () => 'value');

    expect($lazy->resolve())->toBeInstanceOf(LazyProp::class);
})->skip(! class_exists(LazyProp::class), 'Requires Inertia v2 (LazyProp).');

it('resolves to an OptionalProp on Inertia v3', function () {
    $lazy = new InertiaLazy(fn () => 'value');

    expect($lazy->resolve())->toBeInstanceOf(OptionalProp::class);
})->skip(class_exists(LazyProp::class), 'Requires Inertia v3 (LazyProp removed).');

it('resolves to either LazyProp or OptionalProp depending on Inertia version', function () {
    $lazy = new InertiaLazy(fn () => 'value');

    $resolved = $lazy->resolve();

    expect($resolved)->toBeInstanceOf(
        class_exists(LazyProp::class) ? LazyProp::class : OptionalProp::class
    );
});
