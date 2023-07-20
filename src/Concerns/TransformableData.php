<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;

trait TransformableData
{
    protected $_hideAttributes = true;

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
        return json_encode($this->transform(hideProperties: $this->_hideAttributes), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->transform();
    }

    public static function castUsing(array $arguments)
    {
        return new DataEloquentCast(static::class, $arguments);
    }

    public function hideAttributes(bool $hideProperties): static
    {
        $this->_hideAttributes = $hideProperties;

        return $this;
    }
}
