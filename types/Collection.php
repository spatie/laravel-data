<?php

/** @noinspection PhpExpressionResultUnusedInspection */

use function PHPStan\Testing\assertType;

use Spatie\LaravelData\Tests\Fakes\SimpleData;

// Regular collections
$collection = SimpleData::collection(['A', 'B']);
assertType('Spatie\LaravelData\DataCollection<(int|string), Spatie\LaravelData\Tests\Fakes\SimpleData>', $collection);
$collection = SimpleData::collection(collect(['A', 'B']));
assertType('Spatie\LaravelData\DataCollection<(int|string), Spatie\LaravelData\Tests\Fakes\SimpleData>', $collection);

// PaginatedDataCollection
$paginator = \Illuminate\Database\Eloquent\Model::query()->paginate();

$collection = SimpleData::collection($paginator);

assertType('Spatie\LaravelData\PaginatedDataCollection<(int|string), Spatie\LaravelData\Tests\Fakes\SimpleData>', $collection);

// CursorPaginatedDataCollection
$paginator = \Illuminate\Database\Eloquent\Model::query()->cursorPaginate();

$collection = SimpleData::collection($paginator);

assertType('Spatie\LaravelData\CursorPaginatedDataCollection<(int|string), Spatie\LaravelData\Tests\Fakes\SimpleData>', $collection);
