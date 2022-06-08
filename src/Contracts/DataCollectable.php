<?php

namespace Spatie\LaravelData\Contracts;

use Countable;
use IteratorAggregate;
use Spatie\LaravelData\Support\Wrapping\Wrap;

/**
 * @template TValue
 *
 * @extends   \IteratorAggregate<array-key, TValue>
 */
interface DataCollectable extends BaseDataCollectable, ResponsableData, TransformableData, IncludeableData, WrappableData, IteratorAggregate, Countable
{
}
