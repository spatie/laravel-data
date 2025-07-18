<?php

/** @noinspection PhpExpressionResultUnusedInspection */

use function PHPStan\Testing\assertType;

use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDto;
use Spatie\LaravelData\Tests\Fakes\SimpleResource;

$data = SimpleData::from('Hello World');
assertType(SimpleData::class, $data);

$data = SimpleDto::from('Hello World');
assertType(SimpleDto::class, $data);

$data = SimpleResource::from('Hello World');
assertType(SimpleResource::class, $data);
