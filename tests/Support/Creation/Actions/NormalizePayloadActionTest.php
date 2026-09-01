<?php

use Spatie\LaravelData\Normalizers\JsonNormalizer;
use Spatie\LaravelData\Normalizers\ModelNormalizer;
use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\NormalizedModel;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Normalizers\Normalized\UnNormalized;
use Spatie\LaravelData\Support\Creation\Actions\NormalizePayloadAction;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;
use Spatie\LaravelData\Tests\Fakes\Models\FakeNestedModel;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

function normalize(string $dataClass, mixed $value, array $normalizers): array|Normalized
{
    return (new NormalizePayloadAction($normalizers))->execute($value);
}

it('passes arrays through untouched', function () {
    expect(normalize(SimpleData::class, ['a' => 1], []))
        ->toBe(['a' => 1]);
});

it('turns null into an empty array', function () {
    expect(normalize(SimpleData::class, null, []))->toBe([]);
});

it('passes Normalized objects through untouched', function () {
    $normalized = new class () implements Normalized {
        public function getProperty(string $name, DataProperty $dataProperty): mixed
        {
            return UnknownProperty::create();
        }
    };

    expect(normalize(SimpleData::class, $normalized, []))
        ->toBe($normalized);
});

it('runs the normalizer chain, first non-null wins', function () {
    expect(normalize(
        SimpleData::class,
        '{"title": "Hello"}',
        [new ModelNormalizer(), new JsonNormalizer()]
    ))->toBe(['title' => 'Hello']);
});

it('returns the UnNormalized sentinel when no normalizer accepts the value', function () {
    expect(normalize(SimpleData::class, 42, [new JsonNormalizer()]))
        ->toBe(UnNormalized::$instance);
});

it('reads every property of an UnNormalized payload as unknown', function () {
    $property = FakeDataStructureFactory::property(new SimpleData('hello'), 'string');

    expect(UnNormalized::$instance->getProperty('string', $property))
        ->toBe(UnknownProperty::$instance);
});

it('resolves a nested model read into a new NormalizedModel', function () {
    $related = new FakeModel();
    $related->setRawAttributes(['string' => 'Hello']);

    $model = new FakeNestedModel();
    $model->setRelation('fakeModel', $related);

    $relationProperty = FakeDataStructureFactory::property(new class () {
        public ?FakeModel $fakeModel = null;
    }, 'fakeModel');

    [$value] = readProperty([new NormalizedModel($model)], $relationProperty);

    expect($value)->toBe($related);

    $childNormalized = normalize(SimpleData::class, $value, [new ModelNormalizer()]);

    expect($childNormalized)->toBeInstanceOf(NormalizedModel::class);

    $stringProperty = FakeDataStructureFactory::property(new class () {
        public ?string $string = null;
    }, 'string');

    expect(readProperty([$childNormalized], $stringProperty))->toBe(['Hello', 'string']);
});
