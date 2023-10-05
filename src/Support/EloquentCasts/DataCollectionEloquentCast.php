<?php

namespace Spatie\LaravelData\Support\EloquentCasts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotCastData;

class DataCollectionEloquentCast implements CastsAttributes
{
    public function __construct(
        protected string $dataClass,
        protected string $dataCollectionClass = DataCollection::class,
        protected array $arguments = []
    ) {
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

        $data = array_map(
            fn (array $item) => ($this->dataClass)::from($item),
            $data
        );

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

        $data = array_map(
            fn (array | BaseData $item) => is_array($item)
                ? ($this->dataClass)::from($item)
                : $item,
            $value
        );

        $dataCollection = new ($this->dataCollectionClass)($this->dataClass, $data);

        return $dataCollection->toJson();
    }
}
