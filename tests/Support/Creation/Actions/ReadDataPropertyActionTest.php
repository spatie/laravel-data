<?php

use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\Actions\ReadDataPropertyAction;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;

function readProperty(array $normalized, ?DataProperty $property = null, ?CreationContext $context = null): array
{
    return (new ReadDataPropertyAction())->execute(
        $context ?? CreationContextFactory::createFromConfig(SimpleData::class)->get(),
        $property ?? FakeDataStructureFactory::property(new SimpleData('hello'), 'string'),
        $normalized
    );
}

function mappedProperty(): DataProperty
{
    return FakeDataStructureFactory::property(new SimpleDataWithMappedProperty('hello'), 'string');
}

it('reads a present key from an array', function () {
    expect(readProperty([['string' => 'Hello']]))->toBe(['Hello', 'string']);
});

it('reads a present null from an array', function () {
    [$value, $key] = readProperty([['string' => null]]);

    expect($value)->toBeNull()
        ->and($key)->toBe('string');
});

it('returns the UnknownProperty sentinel for a missing key', function () {
    [$value] = readProperty([[]]);

    expect($value)->toBeInstanceOf(UnknownProperty::class);
});

it('returns the UnknownProperty sentinel for an empty list', function () {
    [$value] = readProperty([]);

    expect($value)->toBeInstanceOf(UnknownProperty::class);
});

it('reads properties from a Normalized payload', function () {
    $normalized = new class () implements Normalized {
        public function getProperty(string $name, DataProperty $dataProperty): mixed
        {
            if ($name === 'string') {
                return 'Hello';
            }

            return UnknownProperty::create();
        }
    };

    expect(readProperty([$normalized]))->toBe(['Hello', 'string']);
});

it('the first payload with the key wins', function () {
    expect(readProperty([['string' => 'First'], ['string' => 'Second']]))
        ->toBe(['First', 'string']);
});

it('a null in an earlier payload wins over later values', function () {
    [$value] = readProperty([['string' => null], ['string' => 'Second']]);

    expect($value)->toBeNull();
});

it('an Optional in an earlier payload wins over later values', function () {
    [$value] = readProperty([['string' => Optional::create()], ['string' => 'Second']]);

    expect($value)->toBeInstanceOf(Optional::class);
});

it('payloads missing the key are skipped', function () {
    expect(readProperty([[], ['string' => 'Second']]))->toBe(['Second', 'string']);
});

it('reads the mapped key first and reports it as the original key', function () {
    expect(readProperty([['description' => 'Hello', 'string' => 'ignored']], mappedProperty()))
        ->toBe(['Hello', 'description']);
});

it('falls back to the property name when the mapped key is missing', function () {
    expect(readProperty([['string' => 'Hello']], mappedProperty()))
        ->toBe(['Hello', 'string']);
});

it('reports the mapped key as the original key when nothing is present', function () {
    [$value, $key] = readProperty([[]], mappedProperty());

    expect($value)->toBeInstanceOf(UnknownProperty::class)
        ->and($key)->toBe('description');
});

it('ignores the mapped key when property name mapping is off', function () {
    $context = CreationContextFactory::createFromConfig(SimpleDataWithMappedProperty::class)
        ->withoutPropertyNameMapping()
        ->get();

    [$value] = readProperty([['description' => 'Hello']], mappedProperty(), $context);

    expect($value)->toBeInstanceOf(UnknownProperty::class);
});
