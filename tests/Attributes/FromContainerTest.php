<?php

namespace Spatie\LaravelData\Tests\Attributes;

use Illuminate\Container\Container;
use Spatie\LaravelData\Attributes\FromContainer;
use Spatie\LaravelData\Data;

it('can get the container', function () {
    $dataClass = new class () extends Data {
        #[FromContainer]
        public Container $container;
    };

    expect($dataClass::from()->container)->toBe(Container::getInstance());
});

it('can get a dependency from the container', function () {
    app()->bind('test', fn () => 'test');

    $dataClass = new class () extends Data {
        #[FromContainer(dependency: 'test')]
        public string $test;
    };

    expect($dataClass::from()->test)->toBe('test');
});

it('can get a dependency from the container with parameters', function () {
    app()->bind('test', fn ($app, $parameters) => $parameters['parameter']);

    $dataClass = new class () extends Data {
        #[FromContainer(dependency: 'test', parameters: ['parameter' => 'Hello World'])]
        public string $test;
    };

    expect($dataClass::from()->test)->toBe('Hello World');
});

it('will not set a property when the dependency is not found', function () {
    $dataClass = new class () extends Data {
        #[FromContainer(dependency: 'test')]
        public string $test;
    };

    expect(isset($dataClass::from()->test))->toBeFalse();
});
