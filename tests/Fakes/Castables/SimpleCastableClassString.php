<?php
namespace Spatie\LaravelData\Tests\Fakes\Castables;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\Casts\SimpleCastableClassStringCast;
use Spatie\LaravelData\Tests\Fakes\Casts\StringToUpperCast;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;

class SimpleCastableClassString implements Castable
{
  public function __construct(public string $value) {

  }

  public static function castUsing(...$arguments)
  {
    return SimpleCastableClassStringCast::class;
  }
}
