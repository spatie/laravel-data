<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Database\Eloquent\Model;

class DataFromModelResolver
{
    public function __construct(
        private DataFromArrayResolver $dataFromArrayResolver
    ) {
    }

    public function execute(string $class, Model $model)
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

        return $this->dataFromArrayResolver->execute(
            $class,
            $values
        );
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
