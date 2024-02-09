<?php

namespace Spatie\LaravelData\Support\Caching;

use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;

class CachedDataConfig extends DataConfig
{
    protected ?DataStructureCache $cache = null;

    public function getDataClass(string $class): DataClass
    {
        return $this->cache?->getDataClass($class) ?? parent::getDataClass($class);
    }

    public function setCache(DataStructureCache $cache): self
    {
        $this->cache = $cache;

        return $this;
    }
}
