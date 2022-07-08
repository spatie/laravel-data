<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\Concerns\DataTrait;
use Spatie\LaravelData\Contracts\DataObject;

abstract class Data implements DataObject
{
    use DataTrait;
}
