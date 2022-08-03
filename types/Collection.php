<?php

/** @noinspection PhpExpressionResultUnusedInspection */

use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

use Spatie\LaravelData\CursorPaginatedDataCollection;
use function PHPStan\dumpType;
use function PHPStan\Testing\assertType;

use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

// Regular collections
$collection = SimpleData::collection(['A', 'B']);
assertType(DataCollection::class, $collection);
$collection = SimpleData::collection(collect(['A', 'B']));
assertType(DataCollection::class, $collection);


// PaginatedDataCollection
$items = Collection::times(100, fn (int $index) => "Item {$index}");

$paginator = new LengthAwarePaginator(
    $items->forPage(1, 15),
    100,
    15
);

$collection = SimpleData::collection($paginator);

assertType(PaginatedDataCollection::class, $collection);

// CursorPaginatedDataCollection
$items = Collection::times(100, fn (int $index) => "Item {$index}");

$paginator = new CursorPaginator(
    $items,
    15,
);

$collection = SimpleData::collection($paginator);

assertType(CursorPaginatedDataCollection::class, $collection);
