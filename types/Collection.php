<?php

/** @noinspection PhpExpressionResultUnusedInspection */

use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

use function PHPStan\Testing\assertType;

use Spatie\LaravelData\Tests\Fakes\SimpleData;

// Regular collections
$collection = SimpleData::collection(['A', 'B']);
assertType('Spatie\LaravelData\DataCollection<(int|string), Spatie\LaravelData\Tests\Fakes\SimpleData>', $collection);
$collection = SimpleData::collection(collect(['A', 'B']));
assertType('Spatie\LaravelData\DataCollection<(int|string), Spatie\LaravelData\Tests\Fakes\SimpleData>', $collection);

// PaginatedDataCollection
$items = Collection::times(100, fn (int $index) => "Item {$index}");

$paginator = new LengthAwarePaginator(
    $items->forPage(1, 15),
    100,
    15
);

$collection = SimpleData::collection($paginator);

assertType('Spatie\LaravelData\PaginatedDataCollection<(int|string), Spatie\LaravelData\Tests\Fakes\SimpleData>', $collection);

// CursorPaginatedDataCollection
$items = Collection::times(100, fn (int $index) => "Item {$index}");

$paginator = new CursorPaginator(
    $items,
    15,
);

$collection = SimpleData::collection($paginator);

assertType('Spatie\LaravelData\CursorPaginatedDataCollection<(int|string), Spatie\LaravelData\Tests\Fakes\SimpleData>', $collection);
