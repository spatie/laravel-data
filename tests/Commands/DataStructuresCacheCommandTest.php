<?php

use Illuminate\Support\Facades\App;
use Spatie\LaravelData\Support\Caching\CachedDataConfig;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('can cache data structures', function () {
    config()->set('data.structure_caching.directories', [
        __DIR__.'/../Fakes',
    ]);

    config()->set('data.structure_caching.reflection_discovery.base_path', __DIR__.'/../Fakes');
    config()->set('data.structure_caching.reflection_discovery.root_namespace', 'Spatie\LaravelData\Tests\Fakes');

    $this->artisan('data:cache-structures')->assertExitCode(0);

    expect(cache()->has('laravel-data.config'))->toBeTrue();
    expect(cache()->has('laravel-data.data-class.'. SimpleData::class))->toBeTrue();

    App::forgetInstance(DataConfig::class);

    $config = app(DataConfig::class);

    expect($config)->toBeInstanceOf(CachedDataConfig::class);
    expect($config->getRuleInferrers())->toHaveCount(count(config('data.rule_inferrers')));
    expect(invade($config)->transformers)->toHaveCount(count(config('data.transformers')));
    expect(invade($config)->casts)->toHaveCount(count(config('data.casts')));
});
