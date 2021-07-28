<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class MultiLazyData extends Data
{
    public function __construct(
        public string | Lazy $artist,
        public string | Lazy $name,
        public int | Lazy $year,
    ) {
    }

    public static function create(mixed $model): Data
    {
        return new self(
            Lazy::create(fn () => $model->artist),
            Lazy::create(fn () => $model->name),
            Lazy::create(fn () => $model->year),
        );
    }
}
