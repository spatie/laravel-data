<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapType;

trait WrappableData
{
    protected null|Wrap $_wrap = null;

    public function withoutWrapping(): static
    {
        $this->_wrap = new Wrap(WrapType::Disabled);

        return $this;
    }

    public function wrap(string $key): static
    {
        $this->_wrap = new Wrap(WrapType::Defined, $key);

        return $this;
    }

    public function getWrap(): Wrap
    {
        if ($this->_wrap) {
            return $this->_wrap;
        }

        if (method_exists($this, 'defaultWrap')) {
            return new Wrap(WrapType::Defined, $this->defaultWrap());
        }

        return $this->_wrap ?? new Wrap(WrapType::UseGlobal);
    }
}
