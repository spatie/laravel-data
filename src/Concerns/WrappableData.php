<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapType;

trait WrappableData
{
    public function withoutWrapping(): static
    {
        $this->getDataContext()->wrap = new Wrap(WrapType::Disabled);

        return $this;
    }

    public function wrap(string $key): static
    {
        $this->getDataContext()->wrap = new Wrap(WrapType::Defined, $key);

        return $this;
    }

    public function getWrap(): Wrap
    {
        return $this->getDataContext()->wrap ?? new Wrap(WrapType::UseGlobal);
    }
}
