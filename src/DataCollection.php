<?php

namespace Spatie\LaravelData;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Support\PartialsParser;

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
        $includes = $this->inclusionTree ?? (new PartialsParser())->execute($this->includes);
        $excludes = $this->exclusionTree ?? (new PartialsParser())->execute($this->excludes);

        return array_map(
            function ($item) use ($excludes, $includes) {
                return $this->dataClass::create($item)
                    ->withPartialsTrees($includes, $excludes)
                    ->toArray();
            },
            $this->items instanceof Collection ? $this->items->all() : $this->items
        );
    }
}
