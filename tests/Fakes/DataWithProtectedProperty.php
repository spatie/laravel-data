<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\DataProperty;
use Spatie\LaravelData\Data;

class DataWithProtectedProperty extends Data
{
    #[DataProperty(setter: 'setNonConstructorProperty')]
    protected string $nonConstructorProperty;

    public function __construct(
        #[DataProperty(getter: 'getString')]
        protected string $string,
    ) {
    }

    public function getString(): string
    {
        return $this->string;
    }

    public function getNonConstructorProperty(): string
    {
        return $this->nonConstructorProperty;
    }

    public function setNonConstructorProperty(string $value): void
    {
        $this->nonConstructorProperty = $value;
    }
}
