<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class ModelData extends Data
{
    public function __construct(
        public int $id
    ){
    }

    public static function fromDummyModel(DummyModel $model)
    {
        return new self($model->id);
    }
}
