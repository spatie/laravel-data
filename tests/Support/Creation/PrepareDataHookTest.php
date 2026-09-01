<?php

use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('registers prepareData hooks in order', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareDataHook(fn (array $payloads, string $class, string $path) => [$payloads[0].'-one'])
        ->prepareDataHook(fn (array $payloads, string $class, string $path) => [$payloads[0].'-two'])
        ->get();

    expect($context->prepareDataHooks)->toHaveCount(2);

    $payloads = ['start'];

    foreach ($context->prepareDataHooks as $hook) {
        $payloads = $hook($payloads, SimpleData::class, '');
    }

    expect($payloads)->toBe(['start-one-two']);
});

it('defaults to no prepareData hooks', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)->get();

    expect($context->prepareDataHooks)->toBe([]);
});

it('copies prepareData hooks when deriving a factory from a context', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareDataHook(fn (array $payloads, string $class, string $path) => $payloads)
        ->get();

    $derived = CreationContextFactory::createFromCreationContext(SimpleData::class, $context);

    expect($derived->prepareDataHooks)->toHaveCount(1);
});
