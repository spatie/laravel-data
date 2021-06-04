<?php

namespace Spatie\LaravelData;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;

class DataCollection implements Responsable, Arrayable
{
    use ResponsableData, IncludeableData;

    public function __construct(
        private string $dataClass,
        private Collection | array $items
    ) {
    }

    public function toArray(): array
    {
        return array_map(
            function ($item) {
                return $this->dataClass::create($item)
                    ->include(...$this->includes)
                    ->exclude(...$this->excludes)
                    ->toArray();
            },
            $this->items instanceof Collection ? $this->items->all() : $this->items
        );
    }
}
