<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\UnknownProperty;
use Spatie\LaravelData\Normalizers\Normalizer;
use Spatie\LaravelData\Support\DataProperty;

class FakeNormalizer implements Normalizer
{
    /**
     * @param 'array' | 'array-item' | 'null' | 'unknown-property' | 'non-normalizable' $type
     */
    public function __construct(
        protected string $type = 'array-item',
        protected array $array = [],
    ) {
    }

    public static function nonNormalizeable(): self
    {
        return new self('non-normalizable');
    }

    public static function returnsArray(array $array): self
    {
        return new self('array', $array);
    }

    // Uses Normalized::class
    public static function returnsArrayItem(): self
    {
        return new self('array-item');
    }

    // Uses Normalized::class
    public static function returnsNull(): self
    {
        return new self('null');
    }

    // Uses Normalized::class
    public static function returnsUnknownProperty(): self
    {
        return new self('unknown-property');
    }

    public function normalize(mixed $value): null|array|Normalized
    {
        if ($this->type === 'non-normalizable') {
            return null;
        }

        if ($this->type === 'array') {
            return $this->array;
        }

        return new class ($value, $this->type) implements Normalized {
            public function __construct(
                protected array $value,
                protected string $type,
            ) {
            }

            public function getProperty(string $name, DataProperty $dataProperty): mixed
            {
                return match ($this->type) {
                    'array-item' => $this->value[$name],
                    'null' => null,
                    'unknown-property' => UnknownProperty::create(),
                };
            }
        };
    }
}
