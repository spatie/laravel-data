<?php

namespace Spatie\LaravelData\Tests\Fakes\Livewire;

use Livewire\Component;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;

class MappedDataComponent extends Component
{
    public SimpleDataWithMappedProperty $data;

    public function mount(): void
    {
        $this->data = new SimpleDataWithMappedProperty('Hello World');
    }

    public function save()
    {
        cache()->set('name', $this->data->string);
    }

    public function render()
    {
        return <<<'BLADE'
        <div>
            <input type="text" wire:model.live="data.string">
            <p>{{ $data->string }}</p>
            <button wire:click="save">Save</button>
        </div>
        BLADE;
    }
}
