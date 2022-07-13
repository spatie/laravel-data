<?php

namespace Spatie\LaravelData\Contracts;

use Countable;
use Illuminate\Contracts\Support\Responsable;
use IteratorAggregate;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends   \IteratorAggregate<TKey, TValue>
 */
interface DataCollectable extends Responsable, BaseDataCollectable, ResponsableData, TransformableData, IncludeableData, WrappableData, IteratorAggregate, Countable
{
}
