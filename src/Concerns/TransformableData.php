<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

trait TransformableData
{
    abstract public function transform(
        bool $transformValues = true,
        WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
    ): array;

    public function all(): array
    {
        return $this->transform(transformValues: false);
    }

    public function toArray(): array
    {
        return $this->transform();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
