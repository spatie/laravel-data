<?php

use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('registers prepareData hooks in order', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareData(fn (mixed $payload, string $class, string $path) => $payload.'-one')
        ->prepareData(fn (mixed $payload, string $class, string $path) => $payload.'-two')
        ->get();

    expect($context->prepareData)->toHaveCount(2);

    $value = 'start';

    foreach ($context->prepareData as $hook) {
        $value = $hook($value, SimpleData::class, '');
    }

    expect($value)->toBe('start-one-two');
});

it('defaults to no prepareData hooks', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)->get();

    expect($context->prepareData)->toBe([]);
});

it('copies prepareData hooks when deriving a factory from a context', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareData(fn (mixed $payload, string $class, string $path) => $payload)
        ->get();

    $derived = CreationContextFactory::createFromCreationContext(SimpleData::class, $context);

    expect($derived->prepareData)->toHaveCount(1);
});
