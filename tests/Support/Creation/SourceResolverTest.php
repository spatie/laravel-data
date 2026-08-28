<?php

use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Normalizers\JsonNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;
use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\NormalizedModel;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Support\Creation\SourceReader;
use Spatie\LaravelData\Support\Creation\SourceResolver;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;
use Spatie\LaravelData\Tests\Fakes\Models\FakeNestedModel;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('passes arrays through untouched', function () {
    expect(SourceResolver::resolve(SimpleData::class, ['a' => 1], []))
        ->toBe(['a' => 1]);
});

it('turns null into an empty array', function () {
    expect(SourceResolver::resolve(SimpleData::class, null, []))->toBe([]);
});

it('passes Normalized objects through untouched', function () {
    $normalized = new class () implements Normalized {
        public function getProperty(string $name, DataProperty $dataProperty): mixed
        {
            return UnknownProperty::create();
        }
    };

    expect(SourceResolver::resolve(SimpleData::class, $normalized, []))
        ->toBe($normalized);
});

it('runs the normalizer chain, first non-null wins', function () {
    expect(SourceResolver::resolve(
        SimpleData::class,
        '{"title": "Hello"}',
        [new ModelNormalizer(), new JsonNormalizer()]
    ))->toBe(['title' => 'Hello']);
});

it('throws when no normalizer accepts the value', function () {
    SourceResolver::resolve(SimpleData::class, 42, [new JsonNormalizer()]);
})->throws(CannotCreateData::class);

it('resolves a nested model read into a new NormalizedModel', function () {
    $related = new FakeModel();
    $related->setRawAttributes(['string' => 'Hello']);

    $model = new FakeNestedModel();
    $model->setRelation('fakeModel', $related);

    $relationProperty = FakeDataStructureFactory::property(new class () {
        public ?FakeModel $fakeModel = null;
    }, 'fakeModel');

    $value = SourceReader::read(new NormalizedModel($model), 'fakeModel', $relationProperty);

    expect($value)->toBe($related);

    $childSource = SourceResolver::resolve(SimpleData::class, $value, [new ModelNormalizer()]);

    expect($childSource)->toBeInstanceOf(NormalizedModel::class);

    $stringProperty = FakeDataStructureFactory::property(new class () {
        public ?string $string = null;
    }, 'string');

    expect(SourceReader::read($childSource, 'string', $stringProperty))->toBe('Hello');
});
