<?php

use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
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
