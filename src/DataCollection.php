<?php

namespace Spatie\LaravelData;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Concerns\AppendableData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Transformers\DataCollectionTransformer;

class DataCollection implements Responsable, Arrayable, Jsonable
{
    use ResponsableData, IncludeableData;

    private ?Closure $through = null;

    private ?Closure $filter = null;

    public function __construct(
        private string $dataClass,
        private Collection|array|AbstractPaginator|CursorPaginator $items
    ) {
    }

    public function through(Closure $through): static
    {
        $this->through = $through;

        return $this;
    }

    public function filter(Closure $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    public function all(): array
    {
        return $this->getTransformer()->withoutValueTransforming()->transform();
    }

    public function toArray(): array
    {
        return $this->getTransformer()->transform();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    private function getTransformer(): DataCollectionTransformer
    {
        return new DataCollectionTransformer(
            $this->dataClass,
            $this->getInclusionTree(),
            $this->getExclusionTree(),
            $this->items,
            $this->through,
            $this->filter
        );
    }
}
