<?php

namespace Spatie\LaravelData\Tests\Fakes\Livewire;

use Livewire\Component;
use Spatie\LaravelData\Tests\Fakes\ComputedData;

class ComputedDataComponent extends Component
{
    public ComputedData $data;

    public function mount(): void
    {
        $this->data = new ComputedData('', '');
    }

    public function save()
    {
        // Trigger hydration
    }

    public function render()
    {
        return <<<'BLADE'
        <div>
            <input type="text" wire:model.live="data.first_name">
            <input type="text" wire:model.live="data.last_name">
            <button wire:click="save">Save</button>
            <p>{{ $data->name }}</p>
        </div>
        BLADE;
    }
}
