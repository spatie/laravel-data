<?php

namespace Spatie\LaravelData\Support\EloquentCasts;

use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Spatie\LaravelData\Data;

class DataEloquentCast implements CastsAttributes
{
    public function __construct(
        /** @var class-string<\Spatie\LaravelData\Data> */
        protected string $dataClass
    ) {
    }

    public function get($model, string $key, $value, array $attributes): ?Data
    {
        if ($value === null) {
            return null;
        }

        $payload = json_decode($value, true, flags: JSON_THROW_ON_ERROR);

        /** @var \Spatie\LaravelData\Data $data */
        $data = ($this->dataClass)::create($payload);

        return $data;
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $value = ($this->dataClass)::create($value);
        }

        if (! $value instanceof Data) {
            $className = $model::class;

            throw new Exception("Attribute `{$key}` of model `{$className}` should be a Data object");
        }

        return $value->toJson();
    }
}
