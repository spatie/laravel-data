<?php

namespace Spatie\LaravelData\Support\EloquentCasts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\DataObject;
use Spatie\LaravelData\Exceptions\CannotCastData;

class DataCollectionEloquentCast implements CastsAttributes
{
    public function __construct(
        /** @var class-string<DataObject> $dataClass */
        protected string $dataClass
    ) {
    }

    public function get($model, string $key, $value, array $attributes): ?DataCollection
    {
        if ($value === null) {
            return null;
        }

        $data = json_decode($value, true, flags: JSON_THROW_ON_ERROR);

        $data = array_map(
            fn (array $item) => ($this->dataClass)::from($item),
            $data
        );

        return new DataCollection($this->dataClass, $data);
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DataCollection) {
            $value = $value->all();
        }

        if (! is_array($value)) {
            throw CannotCastData::shouldBeArray($model::class, $key);
        }

        $data = array_map(
            fn (array | DataObject $item) => is_array($item)
                ? ($this->dataClass)::from($item)
                : $item,
            $value
        );

        $dataCollection = new DataCollection($this->dataClass, $data);

        return $dataCollection->toJson();
    }
}
