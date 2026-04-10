<?php

use Spatie\LaravelData\Attributes\PropertyForMorph;
use Spatie\LaravelData\Contracts\PropertyMorphableData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\DataClassFromValidationPayloadResolver;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('returns the data class for non-morphable classes', function () {
    $dataClass = app(DataClassFromValidationPayloadResolver::class)->execute(
        SimpleData::class,
        ['string' => 'Hello'],
        ValidationPath::create(),
    );

    expect($dataClass->name)->toBe(SimpleData::class);
});

it('returns the morphed data class based on the payload', function () {
    abstract class TestResolverAbstractMorphable extends Data implements PropertyMorphableData
    {
        public function __construct(
            #[PropertyForMorph]
            public string $type,
        ) {
        }

        public static function morph(array $properties): ?string
        {
            return match ($properties['type']) {
                'a' => TestResolverMorphableA::class,
                default => null,
            };
        }
    }

    class TestResolverMorphableA extends TestResolverAbstractMorphable
    {
        public function __construct(
            public string $name,
        ) {
            parent::__construct('a');
        }
    }

    $dataClass = app(DataClassFromValidationPayloadResolver::class)->execute(
        TestResolverAbstractMorphable::class,
        ['type' => 'a', 'name' => 'test'],
        ValidationPath::create(),
    );

    expect($dataClass->name)->toBe(TestResolverMorphableA::class);
});

it('returns the abstract data class when morph cannot be resolved', function () {
    $dataClass = app(DataClassFromValidationPayloadResolver::class)->execute(
        TestResolverAbstractMorphable::class,
        ['type' => 'unknown'],
        ValidationPath::create(),
    );

    expect($dataClass->name)->toBe(TestResolverAbstractMorphable::class);
});

it('resolves the morphed data class from a nested path', function () {
    $dataClass = app(DataClassFromValidationPayloadResolver::class)->execute(
        TestResolverAbstractMorphable::class,
        ['nested' => ['type' => 'a', 'name' => 'test']],
        ValidationPath::create('nested'),
    );

    expect($dataClass->name)->toBe(TestResolverMorphableA::class);
});
