<?php

/** @noinspection PhpExpressionResultUnusedInspection */

use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use function PHPStan\Testing\assertType;

// Regular collections
$collection = SimpleData::collect(['A', 'B']);
assertType('array<'.SimpleData::class.'>', $collection);

$collection = SimpleData::collect(collect(['A', 'B']));
assertType(Collection::class.'<(int|string), '.SimpleData::class.'>', $collection);

$collection = SimpleData::collect(new EloquentCollection([new FakeModel(), new FakeModel()]));
assertType(Collection::class.'<(int|string), '.SimpleData::class.'>', $collection);

$collection = SimpleData::collect(new LazyCollection(['A', 'B']));
assertType(LazyCollection::class.'<(int|string), '.SimpleData::class.'>', $collection);

// Paginators

$collection = SimpleData::collect(FakeModel::query()->paginate());
assertType(AbstractPaginator::class.'|'.Enumerable::class.'<(int|string), '.SimpleData::class.'>', $collection);

$collection = SimpleData::collect(FakeModel::query()->cursorPaginate());
assertType(PaginatorContract::class.'|'.AbstractCursorPaginator::class.'|'.Enumerable::class.'<(int|string), '.SimpleData::class.'>', $collection);

# into

$collection = SimpleData::collect(collect(['A', 'B']), 'array');
assertType('array<'.SimpleData::class.'>', $collection);

$collection = SimpleData::collect(['A', 'B'], Collection::class);
assertType(Collection::class.'<(int|string), '.SimpleData::class.'>', $collection);

$collection = SimpleData::collect(['A', 'B'], DataCollection::class);
assertType(DataCollection::class.'<(int|string), '.SimpleData::class.'>', $collection);

$collection = SimpleData::collect(FakeModel::query()->paginate(), PaginatedDataCollection::class);
assertType(PaginatedDataCollection::class.'<(int|string), '.SimpleData::class.'>', $collection);

$collection = SimpleData::collect(FakeModel::query()->paginate(), CursorPaginatedDataCollection::class);
assertType(CursorPaginatedDataCollection::class.'<(int|string), '.SimpleData::class.'>', $collection);

