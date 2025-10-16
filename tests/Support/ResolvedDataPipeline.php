<?php

namespace Spatie\LaravelData\Tests\Normalizers;

use Closure;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Normalizers\Normalizer;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\ResolvedDataPipeline;
use Spatie\LaravelData\Tests\Fakes\FakeNormalizer;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;

it('can resolve the data pipeline', function (
    string $dataClass,
    Normalizer $normalizer,
    array $input,
    Closure $expectations,
    Closure $creationContextModifier = null,
) {
    $pipeline = new ResolvedDataPipeline(
        [$normalizer],
        [],
        app(DataConfig::class)->getDataClass($dataClass),
    );

    $creationContextFactory = CreationContextFactory::createFromConfig($dataClass);

    if ($creationContextModifier) {
        $creationContextModifier($creationContextFactory);
    }

    $properties = $pipeline->execute($input, $creationContextFactory->get());

    $expectations($properties);
})->with(function () {
    yield 'with an array' => [
        SimpleData::class,
        FakeNormalizer::returnsArray([
            'string' => 'value',
        ]),
        ['string' => 'value'],
        fn ($properties) => expect($properties)->toBe(['string' => 'value']),
    ];

    yield 'with an array item in a Normalized object' => [
        SimpleData::class,
        FakeNormalizer::returnsArrayItem(),
        ['string' => 'value'],
        fn ($properties) => expect($properties)->toBe(['string' => 'value']),
    ];

    yield 'with an array item in a Normalized object with input name mapping' => [
        SimpleDataWithMappedProperty::class,
        FakeNormalizer::returnsArrayItem(),
        ['description' => 'value', 'string' => 'not-the-value'],
        fn ($properties) => expect($properties)->toBe(['description' => 'value']), // Real mapping happens in the MapPropertiesPipe
    ];

    yield 'with an array item in a Normalized object with input name mapping disabled' => [
        SimpleDataWithMappedProperty::class,
        FakeNormalizer::returnsArrayItem(),
        ['description' => 'not-the-value', 'string' => 'value'],
        fn ($properties) => expect($properties)->toBe(['string' => 'value']), // Real mapping happens in the MapPropertiesPipe
        fn (CreationContextFactory $factory) => $factory->withoutPropertyNameMapping(),
    ];

    yield 'with an array item in a Normalized object with a null property' => [
        SimpleData::class,
        FakeNormalizer::returnsNull(),
        ['string' => 'value'],
        fn ($properties) => expect($properties)->toBe([
            'string' => null,
        ]),
    ];

    yield 'with an array item in a Normalized object with an unknown property' => [
        SimpleData::class,
        FakeNormalizer::returnsUnknownProperty(),
        ['string' => 'value'],
        fn ($properties) => expect($properties)->toBe([]),
    ];
});

it('will fail when no normalizer could handle the input', function () {
    $pipeline = new ResolvedDataPipeline(
        [FakeNormalizer::nonNormalizeable()],
        [],
        app(DataConfig::class)->getDataClass(SimpleData::class),
    );

    $creationContextFactory = CreationContextFactory::createFromConfig(SimpleData::class);

    $pipeline->execute(['string' => 'value'], $creationContextFactory->get());
})->throws(CannotCreateData::class, 'no normalizer was found');
