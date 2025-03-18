<?php

/** @noinspection PhpExpressionResultUnusedInspection */

use function PHPStan\Testing\assertType;

use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

$factory = SimpleData::factory();
assertType(CreationContextFactory::class.'<'.SimpleData::class.'>', $factory);

// Data

$data = SimpleData::factory()->from('Hello World');
assertType(SimpleData::class, $data);

// Collection

$collection = SimpleData::factory()->collect(['A', 'B']);
assertType('array<0|1, '.SimpleData::class.'>', $collection);

$collection = SimpleData::factory()->collect(['A', 'B'], into: DataCollection::class);
assertType(DataCollection::class.'<int, '.SimpleData::class.'>', $collection);
