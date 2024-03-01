<?php

namespace Spatie\LaravelData\Tests\Fakes\Livewire;

use Livewire\Component;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Tests\Fakes\LazyData;

class SimpleDataComponent extends Component
{
    public LazyData $data;

    public function mount(string|Lazy $name, array $includes = []): void
    {
        $this->data = new LazyData($name);

        $this->data->includePermanently(...$includes);
    }

    public function save()
    {
        cache()->set('name', $this->data->name);
    }

    public function render()
    {
        return <<<'BLADE'
        <div>
            <input type="text" wire:model.live="data.name">
            <p>{{ is_string($data->name) ? $data->name : 'lazy prop' }}</p>
            <button wire:click="save">Save</button>
        </div>
        BLADE;
    }
}
