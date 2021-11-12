<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Spatie\LaravelData\Exceptions\CannotCreateTransformerAttribute;
use Spatie\LaravelData\Transformers\Transformer;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class WithTransformer
{
    public array $arguments;

    public function __construct(
        /** @var class-string<\Spatie\LaravelData\Transformers\Transformer> $transformerClass */
        public string $transformerClass,
        mixed ...$arguments
    ) {
        if (! is_a($this->transformerClass, Transformer::class, true)) {
            throw CannotCreateTransformerAttribute::notATransformer();
        }

        $this->arguments = $arguments;
    }

    public function get(): Transformer
    {
        return new ($this->transformerClass)(...$this->arguments);
    }
}
