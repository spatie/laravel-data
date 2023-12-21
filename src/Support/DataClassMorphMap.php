<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Contracts\BaseData;

class DataClassMorphMap
{
    /** @var array<string, class-string<BaseData>> */
    protected array $map = [];

    /** @var array< class-string<BaseData>, string> */
    protected array $reversedMap = [];


    /**
     * @param string $alias
     * @param class-string<BaseData> $class
     */
    public function add(
        string $alias,
        string $class
    ): self {
        $this->map[$alias] = $class;
        $this->reversedMap[$class] = $alias;

        return $this;
    }

    /**
     * @param array<string, class-string<BaseData>> $map
     */
    public function merge(array|DataClassMorphMap $map): self
    {
        if ($map instanceof DataClassMorphMap) {
            $map->map = array_merge($this->map, $map->map);
            $map->reversedMap = array_merge($this->reversedMap, $map->reversedMap);

            return $this;
        }

        foreach ($map as $alias => $class) {
            $this->add($alias, $class);
        }

        return $this;
    }

    public function getMorphedDataClass(string $alias): ?string
    {
        return $this->map[$alias] ?? null;
    }


    /**
     * @param class-string<BaseData> $class
     */
    public function getDataClassAlias(string $class): ?string
    {
        return $this->reversedMap[$class] ?? null;
    }
}
