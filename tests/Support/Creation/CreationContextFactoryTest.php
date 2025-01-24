<?php

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\Creation\GlobalCastsCollection;
use Spatie\LaravelData\Support\Creation\ValidationStrategy;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\Casts\StringToUpperCast;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('creates a context from the config', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    );

    expect($context->dataClass)->toBe(SimpleData::class);
    expect($context->validationStrategy)->toBe(ValidationStrategy::from(config('data.validation_strategy')));
    expect($context->mapPropertyNames)->toBeTrue();
    expect($context->disableMagicalCreation)->toBeFalse();
    expect($context->ignoredMagicalMethods)->toBeNull();
    expect($context->casts)->toBeNull();
});

it('is possible to override the validation strategy', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    )->validationStrategy(ValidationStrategy::OnlyRequests);

    expect($context->validationStrategy)->toBe(ValidationStrategy::OnlyRequests);
});

it('is possible to disable validation', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    )->withoutValidation();

    expect($context->validationStrategy)->toBe(ValidationStrategy::Disabled);
});

it('is possible to only validate requests', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    )->onlyValidateRequests();

    expect($context->validationStrategy)->toBe(ValidationStrategy::OnlyRequests);
});

it('is possible to always validate', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    )->alwaysValidate();

    expect($context->validationStrategy)->toBe(ValidationStrategy::Always);
});

it('is possible to disable property name mapping', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    )->withoutPropertyNameMapping();

    expect($context->mapPropertyNames)->toBeFalse();
});

it('is possible to enable property name mapping', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    )->withPropertyNameMapping();

    expect($context->mapPropertyNames)->toBeTrue();
});

it('is possible to disable magical creation', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    )->withoutMagicalCreation();

    expect($context->disableMagicalCreation)->toBeTrue();
});

it('is possible to enable magical creation', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    )->withMagicalCreation();

    expect($context->disableMagicalCreation)->toBeFalse();
});

it('is possible to disable optional values', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    )->withoutOptionalValues();

    expect($context->useOptionalValues)->toBeFalse();
});

it('is possible to enable optional values', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    )->withOptionalValues();

    expect($context->useOptionalValues)->toBeTrue();
});

it('is possible to set ignored magical methods', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    )->ignoreMagicalMethod('foo', 'bar');

    expect($context->ignoredMagicalMethods)->toBe(['foo', 'bar']);
});

it('is possible to add a cast', function () {
    $context = CreationContextFactory::createFromConfig(
        SimpleData::class
    )->withCast('string', StringToUpperCast::class);

    $dataClass = new class () extends Data {
        public string $string;
    };

    $dataProperty = app(DataConfig::class)->getDataClass($dataClass::class)->properties['string'];

    expect($context->casts)->not()->toBeNull();

    expect(iterator_to_array($context->casts->findCastsForValue($dataProperty))[0])
        ->toBeInstanceOf(StringToUpperCast::class);
});

it('is possible to add a cast collection', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->withCast(\Illuminate\Support\Stringable::class, StringToUpperCast::class)
        ->withCastCollection(new GlobalCastsCollection([
            'string' => new StringToUpperCast(),
        ]));

    $dataClass = new class () extends Data {
        public string $string;
    };

    $dataProperty = app(DataConfig::class)->getDataClass($dataClass::class)->properties['string'];

    expect($context->casts)->not()->toBeNull();

    expect(iterator_to_array($context->casts->findCastsForValue($dataProperty))[0])
        ->toBeInstanceOf(StringToUpperCast::class);
});
