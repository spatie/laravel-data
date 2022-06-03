<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Contracts\WrappableData as WrappableDataContract;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapType;

trait WrappableData
{
    protected null|Wrap $wrap = null;

    public function withoutWrapping(): WrappableDataContract
    {
        $this->wrap = new Wrap(WrapType::Disabled);

        return $this;
    }

    public function wrap(string $key): WrappableDataContract
    {
        $this->wrap = new Wrap(WrapType::Defined, $key);

        return $this;
    }

    public function getWrap(): Wrap
    {
        if ($this->wrap) {
            return $this->wrap;
        }

        if (method_exists($this, 'defaultWrap')) {
            return new Wrap(WrapType::Defined, $this->defaultWrap());
        }

        return $this->wrap ?? new Wrap(WrapType::UseGlobal);
    }
}
