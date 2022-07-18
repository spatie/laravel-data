<?php

namespace Spatie\LaravelData\Support\Cache;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use ReflectionClass;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Throwable;

class DataCacheManager
{
    public function __construct(
        protected DataConfig $dataConfig,
    ) {
    }

    public function initialize(array $classes)
    {
        /** @var Collection<string> $cachedClasses */
        /** @var Collection<ReflectionClass|string> $uncachedClasses */
        [$cachedClasses, $uncachedClasses] = collect($classes)
            ->partition(fn (ReflectionClass|string $class) => $this->hasClass($class));

        $cachedClasses
            ->map(fn (string $class) => $this->getClass($class))
            ->pipe(fn (Collection $dataClasses) => $this->dataConfig->intializeCachedDataClasses(...$dataClasses->all()));

        $uncachedClasses
            ->map(fn (ReflectionClass|string $class) => $this->createClass($class))
            ->pipe(fn (Collection $dataClasses) => $this->dataConfig->intializeCachedDataClasses(...$dataClasses->all()));
    }

    public function cache(array $classes)
    {
        collect($classes)
            ->map(fn (string $class) => $this->createClass($class))
            ->each(fn (DataClass $dataClass) => $this->putClass($dataClass));
    }

    protected function hasClass(string|ReflectionClass $class): bool
    {
        return $this->getCache()->has($this->getCacheKey($class));
    }

    protected function getClass(string|ReflectionClass $class): DataClass
    {
        try {
            return unserialize($this->getCache()->get($this->getCacheKey($class)));
        } catch (Throwable) {
            return DataClass::create($class);
        }
    }

    protected function putClass(DataClass $dataClass): void
    {
        $this->getCache()->put($this->getCacheKey($dataClass->name), $dataClass);
    }

    protected function createClass(string|ReflectionClass $class): DataClass
    {
        return DataClass::create(
            $class instanceof ReflectionClass ? $class : new ReflectionClass($class)
        );
    }

    protected function cacheClass(DataClass $class): string
    {
        return serialize($class);
    }

    protected function getCacheKey(string|ReflectionClass $class): string
    {
        return 'laravel-data.' . $class instanceof ReflectionClass ? $class->name : $class;
    }

    protected function getCache(): Repository
    {
        return cache()->driver();
    }
}
