<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DataFromModelResolver
{
    public function __construct(
        private DataFromArrayResolver $dataFromArrayResolver
    ) {
    }

    public function execute(string $class, Model $model)
    {
        return $this->dataFromArrayResolver->execute(
            $class,
            $this->serializeModel($model)
        );
    }

    private function serializeModel(Model $model): array
    {
        $values = $model->toArray();

        foreach ($model->getDates() as $key) {
            $values[$key] = $model->getAttribute($key);
        }

        foreach ($model->getCasts() as $key => $cast) {
            if ($this->isDateCast($cast)) {
                $values[$key] = $model->getAttribute($key);
            }
        }

        foreach ($model->getRelations() as $key => $relation) {
            $key = $model::$snakeAttributes ? Str::snake($key) : $key;

            $values[$key] = $relation;
        }

        return $values;
    }

    private function isDateCast(string $cast): bool
    {
        return in_array($cast, [
            'date',
            'datetime',
            'immutable_date',
            'immutable_datetime',
            'custom_datetime',
            'immutable_custom_datetime',
        ]);
    }
}
