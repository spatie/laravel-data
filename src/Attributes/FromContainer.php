<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Illuminate\Container\Container;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Skipped;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FromContainer implements InjectsPropertyValue
{
    public function __construct(
        public ?string $dependency = null,
        public array $parameters = [],
        public bool $replaceWhenPresentInPayload = true
    ) {
    }

    public function resolve(
        DataProperty $dataProperty,
        mixed $payload,
        array $properties,
        CreationContext $creationContext
    ): mixed {
        try {
            $dependency = $this->dependency === null
                ? Container::getInstance()
                : Container::getInstance()->make($this->dependency, $this->parameters);
        } catch (CircularDependencyException|EntryNotFoundException|BindingResolutionException) {
            return Skipped::create();
        }

        return $dependency;
    }

    public function shouldBeReplacedWhenPresentInPayload(): bool
    {
        return $this->replaceWhenPresentInPayload;
    }
}
