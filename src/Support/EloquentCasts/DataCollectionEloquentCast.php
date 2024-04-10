<?php

namespace Spatie\LaravelData\Support\EloquentCasts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Support\DataConfig;

class DataCollectionEloquentCast implements CastsAttributes
{
    protected DataConfig $dataConfig;

    public function __construct(
        protected string $dataClass,
        protected string $dataCollectionClass = DataCollection::class,
        protected array $arguments = []
    ) {
        $this->dataConfig = app(DataConfig::class);
    }

    public function get($model, string $key, $value, array $attributes): ?DataCollection
    {
        if ($value === null && in_array('default', $this->arguments)) {
            $value = '[]';
        }

        if ($value === null) {
            return null;
        }

        $data = json_decode($value, true, flags: JSON_THROW_ON_ERROR);

        $dataClass = $this->dataConfig->getDataClass($this->dataClass);

        $data = array_map(function (array $item) use ($dataClass) {
            if ($dataClass->isAbstract && $dataClass->transformable) {
                $morphedClass = $this->dataConfig->morphMap->getMorphedDataClass($item['type']) ?? $item['type'];

                return $morphedClass::from($item['data']);
            }

            return ($this->dataClass)::from($item);
        }, $data);

        return new ($this->dataCollectionClass)($this->dataClass, $data);
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BaseDataCollectable && $value instanceof TransformableData) {
            $value = $value->all();
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (! is_array($value)) {
            throw CannotCastData::shouldBeArray($model::class, $key);
        }

        $dataClass = $this->dataConfig->getDataClass($this->dataClass);

        $data = array_map(function (array|BaseData $item) use ($dataClass) {
            if ($dataClass->isAbstract && $item instanceof TransformableData) {
                $class = get_class($item);

                return [
                    'type' => $this->dataConfig->morphMap->getDataClassAlias($class) ?? $class,
                    'data' => json_decode(json: $item->toJson(), associative: true, flags: JSON_THROW_ON_ERROR),
                ];
            }

            return is_array($item)
                ? ($this->dataClass)::from($item)
                : $item;
        }, $value);

        if ($dataClass->isAbstract) {
            return json_encode($data);
        }

        $dataCollection = new ($this->dataCollectionClass)($this->dataClass, $data);

        return $dataCollection->toJson();
    }

    protected function isAbstractClassCast(): bool
    {
        return $this->dataConfig->getDataClass($this->dataClass)->isAbstract;
    }
}
