<?php

namespace Spatie\LaravelData\Support\Wrapping;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use TypeError;

enum WrapExecutionType
{
    case Disabled;
    case Enabled;
    case TemporarilyDisabled;

    public function shouldExecute(): bool
    {
        return $this === WrapExecutionType::Enabled;
    }

    public function selectedBy($value): WrapExecutionType
    {
        $isBase = $value instanceof BaseData;
        $isCollection = $value instanceof BaseDataCollectable;

        return match (true) {
            $isBase && $this->name === self::Enabled->name, $isBase && $this->name === self::TemporarilyDisabled->name => self::TemporarilyDisabled,
            $isBase && $this->name === self::Disabled->name, $isCollection && $this->name === self::Disabled->name => self::Disabled,
            $isCollection && $this->name === self::Enabled->name, $isCollection && $this->name === self::TemporarilyDisabled->name => self::Enabled,
            default => throw new TypeError('Invalid wrap execution type')
        };
    }
}
