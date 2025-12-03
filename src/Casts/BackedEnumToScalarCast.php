<?php

namespace Spatie\LaravelData\Casts;

use BackedEnum;
use InvalidArgumentException;
use Spatie\LaravelData\Exceptions\CannotCastScalarFromEnum;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class BackedEnumToScalarCast implements Cast
{
    /**
     * @param 'name'|'value' $output determines whether to return enum value or enum name
     */
    public function __construct(protected string $output = 'value')
    {
        if (! in_array($this->output, ['value', 'name'], true)) {
            throw new InvalidArgumentException('Output must be either "value" or "name".');
        }
    }

    /**
     * Cast enum to scalar (string/int) for serialization.
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): null|int|string
    {
        if ($value instanceof BackedEnum) {
            return $this->output === 'value' ? $value->value : $value->name;
        }

        if (is_string($value) || is_int($value)) {
            return $value;
        }

        throw CannotCastScalarFromEnum::create($this->output, $value);
    }
}
