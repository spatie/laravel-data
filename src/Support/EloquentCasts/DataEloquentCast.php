<?php

namespace Spatie\LaravelData\Support\EloquentCasts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotCastData;

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
        $data = ($this->dataClass)::from($payload);

        return $data;
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $value = ($this->dataClass)::from($value);
        }

        if (! $value instanceof Data) {
            throw CannotCastData::shouldBeData($model::class, $key);
        }

        return $value->toJson();
    }
}
