<?php

namespace Spatie\LaravelData\Contracts;

use Countable;
use IteratorAggregate;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends   \IteratorAggregate<TKey, TValue>
 */
interface DataCollectable extends BaseDataCollectable, ResponsableData, TransformableData, IncludeableData, WrappableData, IteratorAggregate, Countable
{
}
