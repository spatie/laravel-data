<?php

use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\SourceReader;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

function sourceReaderProperty(): DataProperty
{
    return FakeDataStructureFactory::property(new SimpleData('hello'), 'string');
}

it('reads present keys from an array source', function () {
    expect(SourceReader::read(['title' => 'Hello'], 'title', sourceReaderProperty()))
        ->toBe('Hello');
});

it('reads a present null from an array source', function () {
    expect(SourceReader::read(['title' => null], 'title', sourceReaderProperty()))
        ->toBeNull();
});

it('returns the UnknownProperty sentinel for missing array keys', function () {
    expect(SourceReader::read([], 'title', sourceReaderProperty()))
        ->toBeInstanceOf(UnknownProperty::class);
});

it('reads properties from a Normalized source', function () {
    $normalized = new class () implements Normalized {
        public function getProperty(string $name, DataProperty $dataProperty): mixed
        {
            if ($name === 'title') {
                return 'Hello';
            }

            return UnknownProperty::create();
        }
    };

    expect(SourceReader::read($normalized, 'title', sourceReaderProperty()))->toBe('Hello')
        ->and(SourceReader::read($normalized, 'missing', sourceReaderProperty()))
        ->toBeInstanceOf(UnknownProperty::class);
});

it('later sources override earlier ones', function () {
    expect(SourceReader::readFromMany(
        [['title' => 'First'], ['title' => 'Second']],
        'title',
        sourceReaderProperty()
    ))->toBe('Second');
});

it('null never overwrites an existing value', function () {
    expect(SourceReader::readFromMany(
        [['title' => 'First'], ['title' => null]],
        'title',
        sourceReaderProperty()
    ))->toBe('First');
});

it('Optional never overwrites an existing value', function () {
    expect(SourceReader::readFromMany(
        [['title' => 'First'], ['title' => Optional::create()]],
        'title',
        sourceReaderProperty()
    ))->toBe('First');
});

it('null wins when it is the only present value', function () {
    expect(SourceReader::readFromMany(
        [[], ['title' => null]],
        'title',
        sourceReaderProperty()
    ))->toBeNull();
});

it('sources missing the key are skipped', function () {
    expect(SourceReader::readFromMany(
        [['title' => 'First'], []],
        'title',
        sourceReaderProperty()
    ))->toBe('First');
});

it('returns the UnknownProperty sentinel when no source has the key', function () {
    expect(SourceReader::readFromMany(
        [[], []],
        'title',
        sourceReaderProperty()
    ))->toBeInstanceOf(UnknownProperty::class);
});
