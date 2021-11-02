<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\Exceptions\InvalidDataClass;
use Spatie\LaravelData\Resolvers\DataFromSomethingResolver;

trait WithData
{
    public function getData(): Data
    {
        $dataClass = match (true) {
            /** @psalm-suppress UndefinedThisPropertyFetch */
            property_exists($this, 'dataClass') => $this->dataClass,
            method_exists($this, 'dataClass') => $this->dataClass(),
            default => null,
        };

        if (! is_a($dataClass, Data::class, true)) {
            throw InvalidDataClass::create($dataClass);
        }

        return resolve(DataFromSomethingResolver::class)->execute(
            $dataClass,
            $this
        );
    }
}
