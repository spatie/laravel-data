<?php

use Illuminate\Support\Facades\App;
use Spatie\LaravelData\Support\Caching\CachedDataConfig;
use Spatie\LaravelData\Support\Caching\DataStructureCache;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('can cache data structures', function () {
    // Ensure we cache
    App::forgetInstance(DataConfig::class);
    app()->singleton(
        DataConfig::class,
        function () {
            return app()->make(DataStructureCache::class)->getConfig() ?? DataConfig::createFromConfig(config('data'));
        }
    );

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
    expect($config->ruleInferrers)->toHaveCount(count(config('data.rule_inferrers')));
    expect(invade($config)->transformers)->toHaveCount(count(config('data.transformers')));
    expect(invade($config)->casts)->toHaveCount(count(config('data.casts')));
});

it('will ignore non existing directories', function () {
    // Ensure we cache
    App::forgetInstance(DataConfig::class);

    app()->singleton(
        DataConfig::class,
        function () {
            return app()->make(DataStructureCache::class)->getConfig() ?? DataConfig::createFromConfig(config('data'));
        }
    );

    config()->set('data.structure_caching.directories', [
        __DIR__.'/../Fakes',
        __DIR__.'/../NonExisting',
    ]);

    config()->set('data.structure_caching.reflection_discovery.base_path', __DIR__.'/../Fakes');
    config()->set('data.structure_caching.reflection_discovery.root_namespace', 'Spatie\LaravelData\Tests\Fakes');

    $this->artisan('data:cache-structures')->assertExitCode(0);

    expect(cache()->has('laravel-data.data-class.'. SimpleData::class))->toBeTrue();
});
