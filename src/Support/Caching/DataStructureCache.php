<?php

namespace Spatie\LaravelData\Support\Caching;

use Illuminate\Contracts\Cache\Store;
use Spatie\LaravelData\Support\DataClass;
use Throwable;

class DataStructureCache
{
    protected Store $store;

    private string $prefix;

    public function __construct(
        protected array $cacheConfig,
    ) {
        $this->store = cache()->store($this->cacheConfig['store'])?->getStore();
        $this->prefix = $this->cacheConfig['prefix'] ? "{$this->cacheConfig['prefix']}." : '';
    }

    public function getConfig(): ?CachedDataConfig
    {
        $serialized = $this->store->get("{$this->prefix}config");

        if ($serialized === null) {
            return null;
        }

        try {
            /** @var CachedDataConfig $cachedConfig */
            $cachedConfig = unserialize($serialized);

            $cachedConfig->setCache($this);

            return $cachedConfig;
        } catch (Throwable) {
            return null;
        }
    }

    public function storeConfig(CachedDataConfig $config): void
    {
        $this->store->forever(
            "{$this->prefix}config",
            serialize($config),
        );
    }

    public function getDataClass(string $className): ?DataClass
    {
        $serialized = $this->store->get("{$this->prefix}data-class.{$className}");

        if ($serialized === null) {
            return null;
        }

        try {
            return unserialize($serialized);
        } catch (Throwable) {
            return null;
        }
    }

    public function storeDataClass(DataClass $dataClass): void
    {
        $this->store->forever(
            "{$this->prefix}data-class.{$dataClass->name}",
            serialize($dataClass),
        );
    }
}
