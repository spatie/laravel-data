<?php

namespace Spatie\LaravelData\Support\Lazy;

use Exception;
use Spatie\LaravelData\Lazy;

class LivewireLostLazy extends Lazy
{
    public function __construct(
        public string $dataClass,
        public string $propertyName
    ) {
    }

    public function resolve(): mixed
    {
        return throw new Exception("Lazy property `{$this->dataClass}::{$this->propertyName}` was lost when the data object was transformed to be used by Livewire. You can include the property and then the correct value will be set when creating the data object from Livewire again.");
    }

    public function __serialize(): array
    {
        return [
            'dataClass' => $this->dataClass,
            'propertyName' => $this->propertyName,
            'defaultIncluded' => $this->defaultIncluded,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->dataClass = $data['dataClass'];
        $this->propertyName = $data['propertyName'];
        $this->defaultIncluded = $data['defaultIncluded'];
    }
}
