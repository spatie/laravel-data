<?php

/** @noinspection PhpExpressionResultUnusedInspection */

use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDto;
use Spatie\LaravelData\Tests\Fakes\SimpleResource;
use function PHPStan\Testing\assertType;

$factory = SimpleData::factory();
assertType(CreationContextFactory::class.'<'.SimpleData::class.'>', $factory);

// Data

$data = SimpleData::factory()->from('Hello World');
assertType(SimpleData::class, $data);

// Collection

$collection = SimpleData::factory()->collect(['A', 'B']);
assertType('array<int, '.SimpleData::class.'>', $collection);

$collection = SimpleData::factory()->collect(['A', 'B'], into: DataCollection::class);
assertType(DataCollection::class.'<int, '.SimpleData::class.'>', $collection);
