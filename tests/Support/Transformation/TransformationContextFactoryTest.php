<?php

use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\Transformers\StringToUpperTransformer;

it('can create a transformation context', function () {
    $context = TransformationContextFactory::create()->get(
        SimpleData::from('Hello World')
    );

    expect($context)->toBeInstanceOf(TransformationContext::class);
    expect($context->transformValues)->toBeTrue();
    expect($context->mapPropertyNames)->toBeTrue();
    expect($context->wrapExecutionType)->toBe(WrapExecutionType::Disabled);
    expect($context->transformers)->toBeNull();
    expect($context->depth)->toBe(0);
    expect($context->maxDepth)->toBeNull();
    expect($context->failWhenMaxDepthReached)->toBeTrue();
});

it('can disable value transformation', function () {
    $context = TransformationContextFactory::create()
        ->withoutValueTransformation()
        ->get(SimpleData::from('Hello World'));

    expect($context->transformValues)->toBeFalse();
});

it('can enable value transformation', function () {
    $context = TransformationContextFactory::create()
        ->withValueTransformation()
        ->get(SimpleData::from('Hello World'));

    expect($context->transformValues)->toBeTrue();
});

it('can disable property name mapping', function () {
    $context = TransformationContextFactory::create()
        ->withoutPropertyNameMapping()
        ->get(SimpleData::from('Hello World'));

    expect($context->mapPropertyNames)->toBeFalse();
});

it('can enable property name mapping', function () {
    $context = TransformationContextFactory::create()
        ->withPropertyNameMapping()
        ->get(SimpleData::from('Hello World'));

    expect($context->mapPropertyNames)->toBeTrue();
});

it('can disable wrapping', function () {
    $context = TransformationContextFactory::create()
        ->withoutWrapping()
        ->get(SimpleData::from('Hello World'));

    expect($context->wrapExecutionType)->toBe(WrapExecutionType::Disabled);
});

it('can enable wrapping', function () {
    $context = TransformationContextFactory::create()
        ->withWrapping()
        ->get(SimpleData::from('Hello World'));

    expect($context->wrapExecutionType)->toBe(WrapExecutionType::Enabled);
});

it('can set a custom wrap execution type', function () {
    $context = TransformationContextFactory::create()
        ->withWrapExecutionType(WrapExecutionType::Enabled)
        ->get(SimpleData::from('Hello World'));

    expect($context->wrapExecutionType)->toBe(WrapExecutionType::Enabled);
});

it('can add a custom transformers', function () {
    $context = TransformationContextFactory::create()
        ->withTransformer('string', StringToUpperTransformer::class)
        ->get(SimpleData::from('Hello World'));

    expect($context->transformers)->not()->toBe(null);
    expect($context->transformers->findTransformerForValue('Hello World'))->toBeInstanceOf(StringToUpperTransformer::class);
});

it('can set a max transformation depth', function () {
    $context = TransformationContextFactory::create()
        ->maxDepth(4)
        ->get(SimpleData::from('Hello World'));

    expect($context->maxDepth)->toBe(4);
    expect($context->depth)->toBe(0);
    expect($context->failWhenMaxDepthReached)->toBeTrue();
});

it('can set a max transformation depth without failing', function () {
    $context = TransformationContextFactory::create()
        ->maxDepth(4, fail: false)
        ->get(SimpleData::from('Hello World'));

    expect($context->maxDepth)->toBe(4);
    expect($context->depth)->toBe(0);
    expect($context->failWhenMaxDepthReached)->toBeFalse();
});
