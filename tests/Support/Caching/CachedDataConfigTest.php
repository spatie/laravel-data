<?php

use Illuminate\Support\Facades\App;
use Mockery\MockInterface;
use Spatie\LaravelData\Support\Caching\CachedDataConfig;
use Spatie\LaravelData\Support\Caching\DataStructureCache;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('will use a cached data config if available', function () {
    $cache = app(DataStructureCache::class);

    $cachedDataConfig = new CachedDataConfig();

    $cache->storeConfig($cachedDataConfig);

    $config = app(DataConfig::class);

    expect($config)->toBeInstanceOf(CachedDataConfig::class);
});

it('will use a non cached config when a cached version is not available', function () {
    $config = app(DataConfig::class);

    expect($config)->toBeInstanceOf(DataConfig::class);
});

it('will use a cached data config if the cached version is invalid', function () {
    ['store' => $store, 'prefix' => $prefix] = config('data.structure_caching.cache');

    cache()->store($store)->forever("{$prefix}.config", serialize(new CachedDataConfig()));

    expect(app(DataConfig::class))->toBeInstanceOf(CachedDataConfig::class);

    cache()->store($store)->forever("{$prefix}.config", 'invalid');

    App::forgetInstance(DataConfig::class);

    expect(app(DataConfig::class))->toBeInstanceOf(DataConfig::class);
});

it('will load cached data classes', function () {
    $dataClass = DataClass::create(new ReflectionClass(SimpleData::class));
    $dataClass->prepareForCache();

    $mock = Mockery::mock(
        new DataStructureCache(config('data.structure_caching.cache')),
        function (MockInterface $spy) use ($dataClass) {
            $spy
                ->shouldReceive('getDataClass')
                ->with(SimpleData::class)
                ->andReturn($dataClass)
                ->once();
        }
    )->makePartial()->shouldAllowMockingProtectedMethods();

    $cachedDataConfig = (new CachedDataConfig())->setCache($mock);

    $mock->storeDataClass($dataClass);

    $cachedDataClass = $cachedDataConfig->getDataClass(SimpleData::class);

    expect($cachedDataClass)
        ->toBeInstanceOf(DataClass::class)
        ->name->toBe(SimpleData::class);
});
