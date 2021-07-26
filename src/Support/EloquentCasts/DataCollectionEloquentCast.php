<?php

namespace Spatie\LaravelData\Support\EloquentCasts;

use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class DataCollectionEloquentCast implements CastsAttributes
{
    public function __construct(
        /** @var class-string<\Spatie\LaravelData\Data> */
        protected string $dataClass
    )
    {
    }

    public function get($model, string $key, $value, array $attributes): ?DataCollection
    {
        if($value === null){
            return null;
        }

        $data = json_decode($value, true, flags: JSON_THROW_ON_ERROR);

        $data = array_map(
            fn(array $item) => ($this->dataClass)::createFromArray($item),
            $data
        );

        return new DataCollection($this->dataClass, $data);
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if($value === null){
            return null;
        }

        if($value instanceof DataCollection){
            $value = $value->all();
        }

        if(!is_array($value)){
            throw new Exception("Value given to a data collection eloquent cast should be a DataCollection or array");
        }

        $data = array_map(
            fn(array|Data $item) => is_array($item)
                ? ($this->dataClass)::createFromArray($item)
                : $item,
            $value
        );

        $dataCollection = new DataCollection($this->dataClass, $data);

        return $dataCollection->toJson();
    }
}
