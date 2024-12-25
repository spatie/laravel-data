<?php

namespace Spatie\LaravelData\Tests\Fakes\Collections;

use Illuminate\Support\Collection;

/**
 * @template TKey of array-key
 * @template TData of \Spatie\LaravelData\Tests\Fakes\SimpleData
 *
 * @extends \Illuminate\Support\Collection<TKey, TData>
 */
class SimpleDataCollectionWithAnnotations extends Collection
{
}
