<?php

namespace Spatie\LaravelData;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;

class PaginatedDataCollection implements Arrayable, Responsable
{
    use ResponsableData, IncludeableData;

    public function __construct(
        /** @var \Spatie\LaravelData\Data */
        private string $dataClass,
        private Collection | LengthAwarePaginator $items
    ) {
    }

    public function toArray()
    {
        return [
            'data' => $this->resolveData(),
            'meta' => $this->resolveMeta(),
        ];
    }

    private function resolveData(): array
    {
        return array_map(
            function ($item) {
                return $this->dataClass::create($item)
                    ->include(...$this->includes)
                    ->exclude(...$this->excludes)
                    ->toArray();
            },
            $this->items->all()
        );
    }

    private function resolveMeta(): array
    {
        if (! $this->items instanceof LengthAwarePaginator) {
            return [];
        }

        return [
            'current_page' => $this->items->currentPage(),
            'first_page_url' => $this->items->url(1),
            'from' => $this->items->firstItem(),
            'last_page' => $this->items->lastPage(),
            'last_page_url' => $this->items->url($this->items->lastPage()),
            'next_page_url' => $this->items->nextPageUrl(),
            'path' => $this->items->path(),
            'per_page' => $this->items->perPage(),
            'prev_page_url' => $this->items->previousPageUrl(),
            'to' => $this->items->lastItem(),
            'total' => $this->items->total(),
        ];
    }
}
