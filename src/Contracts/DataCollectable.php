<?php

namespace Spatie\LaravelData\Contracts;

use Countable;
use IteratorAggregate;
use Spatie\LaravelData\Support\Wrapping\Wrap;

interface DataCollectable extends ResponsableData, TransformableData, IncludeableData, WrappableData, IteratorAggregate, Countable
{

}
