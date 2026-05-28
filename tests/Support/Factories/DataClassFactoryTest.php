<?php

namespace Spatie\LaravelData\Tests\Support\Factories;

use ReflectionClass;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Factories\DataClassFactory;

it('can create a data class with defaults from promoted properties', function () {
    class PromotedPropertyData extends Data {
        public function __construct(
            public string $promotedProperty = 'hello',
            protected string $protectedPromotedProperty = 'hello',
            string $property = 'hello',
        ) {
        }
    }

    $factory = app(DataClassFactory::class);
    $dataClass = $factory->build(new ReflectionClass(PromotedPropertyData::class));

    expect($dataClass->properties['promotedProperty'] ?? null)
        ->toBeInstanceOf(DataProperty::class)
        ->toHaveProperty('defaultValue', 'hello');

    expect($dataClass->properties)
        ->not()
        ->toHaveProperty('protectedPromotedProperty');
});

it('can create a data class with defaults from inherited promoted properties', function () {
    abstract class ParentNestedPromotedPropertyData extends Data {
        public function __construct(
            public string $promotedProperty = 'hello',
            public string $promotedPropertyWithOverride = 'hello',
        ) {
        }
    }

    class NestedPromotedPropertyData extends ParentNestedPromotedPropertyData {
        public function __construct(
            string $promotedProperty = 'hello from child',
            public string $promotedPropertyWithOverride = 'hello with override from child',
        ) {
            parent::__construct($promotedProperty, $promotedPropertyWithOverride);
        }
    }

    $factory = app(DataClassFactory::class);
    $dataClass = $factory->build(new ReflectionClass(NestedPromotedPropertyData::class));

    expect($dataClass->properties['promotedProperty'] ?? null)
        ->toBeInstanceOf(DataProperty::class)
        ->toHaveProperty('defaultValue', 'hello');

    expect($dataClass->properties['promotedPropertyWithOverride'] ?? null)
        ->toBeInstanceOf(DataProperty::class)
        ->toHaveProperty('defaultValue', 'hello with override from child');
});
