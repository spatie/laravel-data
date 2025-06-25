<?php

namespace Spatie\LaravelData\Support\Validation\References;

use Illuminate\Container\Container;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\CircularDependencyException;

class ContainerReference implements ExternalReference
{
    public function __construct(
        public string $dependency,
        public ?string $property = null,
        public array $parameters = [],
    ) {
    }

    public function getValue(): mixed
    {
        try {
            $dependency = Container::getInstance()->make($this->dependency, $this->parameters);
        } catch (CircularDependencyException|EntryNotFoundException|BindingResolutionException) {
            return null;
        }

        if ($this->property !== null) {
            return data_get($dependency, $this->property);
        }

        return $dependency;
    }
}
