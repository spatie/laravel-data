<?php

namespace Spatie\LaravelData\Tests\Fakes\Livewire;

use Livewire\Component;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Tests\Fakes\NestedLazyData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class NestedDataComponent extends Component
{
    public NestedLazyData $data;

    public function mount(SimpleData|Lazy $nested, array $includes = []): void
    {
        $this->data = new NestedLazyData($nested);

        $this->data->includePermanently(...$includes);
    }

    public function save()
    {
        cache()->set('name', $this->data->simple->string);
    }

    public function render()
    {
        return <<<'BLADE'
        <div>
            <input type="text" wire:model.live="data.simple.string">
            <p>{{ $data->simple->string }}</p>
            <button wire:click="save">Save</button>
        </div>
        BLADE;
    }
}
