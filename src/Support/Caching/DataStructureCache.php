<?php

namespace Spatie\LaravelData\Support\Caching;

use Illuminate\Contracts\Cache\Store;
use Spatie\LaravelData\Support\DataClass;
use Throwable;

class DataStructureCache
{
    protected Store $store;

    private string $prefix;

    private ?int $duration;

    public function __construct(
        protected array $cacheConfig,
    ) {
        $this->store = cache()->store(($this->cacheConfig['store'] ?? null))?->getStore();
        $this->prefix = ($this->cacheConfig['prefix'] ?? '') ? "{$this->cacheConfig['prefix']}." : '';
        $this->duration = $this->cacheConfig['duration'] ?? null;
    }

    public function getConfig(): ?CachedDataConfig
    {
        /** @var ?CachedDataConfig $cachedConfig */
        $cachedConfig = $this->get('config');

        if ($cachedConfig) {
            $cachedConfig->setCache($this);
        }

        return $cachedConfig;
    }

    public function storeConfig(CachedDataConfig $config): void
    {
        $this->set('config', $config);
    }

    public function getDataClass(string $className): ?DataClass
    {
        return $this->get("data-class.{$className}");
    }

    public function storeDataClass(DataClass $dataClass): void
    {
        $this->set("data-class.{$dataClass->name}", $dataClass);
    }

    private function get(string $key): mixed
    {
        $serialized = $this->store->get($this->prefix . $key);

        if ($serialized === null) {
            return null;
        }

        try {
            return unserialize($serialized);
        } catch (Throwable) {
            return null;
        }
    }

    private function set(string $key, mixed $value): void
    {
        if (is_null($this->duration)) {
            $this->store->forever($this->prefix . $key, serialize($value));
        } else {
            $this->store->put($this->prefix . $key, serialize($value), $this->duration);
        }
    }
}
