<?php

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Support\DataParameter;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\DataWithMultipleArgumentCreationMethod;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('can create a data method from a constructor', function () {
    $class = new class () extends Data {
        public function __construct(
            public string $promotedProperty = 'hello',
            protected string $protectedPromotedProperty = 'hello',
            string $property = 'hello',
        ) {
        }
    };

    $method = DataMethod::createConstructor(
        new ReflectionMethod($class, '__construct'),
        collect(['promotedProperty' => DataProperty::create(new ReflectionProperty($class, 'promotedProperty'))])
    );

    expect($method)
        ->name->toEqual('__construct')
        ->parameters->toHaveCount(2)
        ->isPublic->toBeTrue()
        ->isStatic->toBeFalse()
        ->isCustomCreationMethod->toBeFalse()
        ->and($method->parameters[0])->toBeInstanceOf(DataProperty::class)
        ->and($method->parameters[1])->toBeInstanceOf(DataParameter::class);
});

it('can create a data method from a magic method', function () {
    $class = new class () extends Data {
        public static function fromString(
            string $property,
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    expect($method)
        ->name->toEqual('fromString')
        ->parameters->toHaveCount(1)
        ->isPublic->toBeTrue()
        ->isStatic->toBeTrue()
        ->isCustomCreationMethod->toBeTrue()
        ->and($method->parameters[0])->toBeInstanceOf(DataParameter::class);
});

it('correctly accepts single values as magic creation method', function () {
    $class = new class () extends Data {
        public static function fromString(
            string $property,
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    expect($method)
        ->accepts('Hello')->toBeTrue()
        ->accepts(3.14)->toBeFalse();
});

it('correctly accepts single inherited values as magic creation method', function () {
    $class = new class () extends Data {
        public static function fromString(
            Data $property,
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    expect($method->accepts(new SimpleData('Hello')))->toBeTrue();
});

it('correctly accepts multiple values as magic creation method', function () {
    $method = DataMethod::create(new ReflectionMethod(DataWithMultipleArgumentCreationMethod::class, 'fromMultiple'));

    expect($method)
        ->accepts('Hello', 42)->toBeTrue()
        ->accepts(...[
            'number' => 42,
            'string' => 'hello',
        ])->toBeTrue()
        ->accepts(42, 'Hello')->toBeFalse();
});

it('correctly accepts mixed values as magic creation method', function () {
    $class = new class () extends Data {
        public static function fromString(
            mixed $property,
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    expect($method)
        ->accepts(new SimpleData('Hello'))->toBeTrue()
        ->accepts(null)->toBeTrue();
});

it('correctly accepts values with defaults as magic creation method', function () {
    $class = new class () extends Data {
        public static function fromString(
            string $property = 'Hello',
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    expect($method)
        ->accepts('Hello')->toBeTrue()
        ->accepts()->toBeTrue();
});

it('needs a correct amount of parameters as magic creation method', function () {
    $class = new class () extends Data {
        public static function fromString(
            string $property,
            string $propertyWithDefault = 'Hello',
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    expect($method)
        ->accepts('Hello')->toBeTrue()
        ->accepts('Hello', 'World')->toBeTrue()
        ->accepts()->toBeFalse()
        ->accepts('Hello', 'World', 'Nope')->toBeFalse();
});
