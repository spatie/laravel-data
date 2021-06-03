<?php

namespace Spatie\LaravelData;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;

class DataCollection implements Responsable, Arrayable
{
    use ResponsableData;

    private array $includes = [];

    public function __construct(
        private string $dataClass,
        private Collection | array $items
    ) {
    }

    public function include(string ...$includes): static
    {
        $this->includes = array_merge($this->includes, $includes);

        return $this;
    }

    public function toArray(): array
    {
        return array_map(
            function ($item) {
                $data = $this->dataClass::create($item);

                return $data->include(...$this->includes)->toArray();
            },
            $this->items instanceof Collection ? $this->items->all() : $this->items
        );
    }
}
