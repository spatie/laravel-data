<?php

namespace Spatie\LaravelData\Support\Caching;

use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;

class CachedDataConfig extends DataConfig
{
    protected ?DataStructureCache $cache = null;

    public function __construct()
    {
        parent::__construct([
            'rule_inferrers' => [],
            'transformers' => [],
            'casts' => [],
        ]); // Ensure the parent object is constructed empty, todo v4: remove this and use a better constructor with factory
    }

    public function getDataClass(string $class): DataClass
    {
        if (array_key_exists($class, $this->dataClasses)) {
            return $this->dataClasses[$class];
        }

        if ($this->cache === null) {
            return parent::getDataClass($class);
        }

        $dataClass = $this->cache->getDataClass($class);

        if ($dataClass === null) {
            return parent::getDataClass($class);
        }

        return $this->dataClasses[$class] = $dataClass;
    }

    public function setCache(DataStructureCache $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    public static function initialize(
        DataConfig $dataConfig
    ): self {
        $cachedConfig = new self();

        $cachedConfig->ruleInferrers = $dataConfig->ruleInferrers;
        $cachedConfig->transformers = $dataConfig->transformers;
        $cachedConfig->casts = $dataConfig->casts;

        $cachedConfig->dataClasses = [];
        $cachedConfig->resolvedDataPipelines = [];

        $dataConfig->morphMap->merge($cachedConfig->morphMap);

        return $cachedConfig;
    }
}
