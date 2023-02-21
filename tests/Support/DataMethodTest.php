<?php

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use phpDocumentor\Reflection\Types\Self_;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Enums\CustomCreationMethodType;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Support\DataParameter;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Type;
use Spatie\LaravelData\Tests\Fakes\DataWithMultipleArgumentCreationMethod;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('can create a data method from a constructor', function () {
    $class = new class () extends Data {
        public function __construct(
            public string $promotedProperty = 'hello',
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
        ->customCreationMethodType->toBe(CustomCreationMethodType::None)
        ->and($method->parameters[0])->toBeInstanceOf(DataProperty::class)
        ->and($method->parameters[1])->toBeInstanceOf(DataParameter::class);
});

it('can create a data method from a magic method', function () {
    $class = new class () extends Data {
        public static function fromString(
            string $property,
        ): self {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'fromString'));

    expect($method)
        ->name->toEqual('fromString')
        ->parameters->toHaveCount(1)
        ->isPublic->toBeTrue()
        ->isStatic->toBeTrue()
        ->customCreationMethodType->toBe(CustomCreationMethodType::Object)
        ->and($method->parameters[0])->toBeInstanceOf(DataParameter::class);
});

it('can create a data method from a magic collect method', function () {
    $class = new class () extends Data {
        public static function collectArray(
            array $items,
        ): array {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'collectArray'));

    expect($method)
        ->name->toEqual('collectArray')
        ->parameters->toHaveCount(1)
        ->isPublic->toBeTrue()
        ->isStatic->toBeTrue()
        ->customCreationMethodType->toBe(CustomCreationMethodType::Collection)
        ->and($method->parameters[0])->toBeInstanceOf(DataParameter::class);

    expect($method->returnType)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->getAcceptedTypes()->toBe(['array' => []]);
});

it('can create a data method from a magic collect method with nullable return type', function () {
    $class = new class () extends Data {
        public static function collectArray(
            array $items,
        ): ?array {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'collectArray'));

    expect($method)
        ->customCreationMethodType->toBe(CustomCreationMethodType::Collection);

    expect($method->returnType)
        ->isNullable->toBeTrue()
        ->isMixed->toBeFalse()
        ->getAcceptedTypes()->toBe(['array' => []]);
});

it('will not create a magical collection method when no return type specified', function () {
    $class = new class () extends Data {
        public static function collectArray(
            array $items,
        ) {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'collectArray'));

    expect($method)
        ->customCreationMethodType->toBe(CustomCreationMethodType::None);

    expect($method->returnType)
        ->isNullable->toBeTrue()
        ->isMixed->toBeTrue()
        ->getAcceptedTypes()->toBe([]);
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

it('can check if a magical method can return the exact type', function () {
    $class = new class () extends Data {
        public static function collectCollection(
            Collection $property,
        ): Collection {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'collectCollection'));

    expect($method->returns(Collection::class))->toBeTrue();
});

it('can check if a magical method can return the sub type', function () {
    $class = new class () extends Data {
        public static function collectCollection(
            Collection $property,
        ): Collection {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'collectCollection'));

    expect($method->returns(EloquentCollection::class))->toBeTrue();
});

it('can check if a magical method can return a built in type', function () {
    $class = new class () extends Data {
        public static function collectCollectionToArray(
            Collection $property,
        ): array {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'collectCollectionToArray'));

    expect($method->returns('array'))->toBeTrue();
});


it('can check if a magical method cannot return a parent type', function () {
    $class = new class () extends Data {
        public static function collectCollection(
            Collection $property,
        ): Collection {
        }
    };

    $method = DataMethod::create(new ReflectionMethod($class, 'collectCollection'));

    expect($method->returns(Enumerable::class))->toBeFalse();
});
