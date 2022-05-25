<?php

namespace Spatie\LaravelData;

use Illuminate\Contracts\Database\Eloquent\Castable as EloquentCastable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use JsonSerializable;

interface DataObject extends Arrayable, Responsable, Jsonable, EloquentCastable, JsonSerializable
{

}
