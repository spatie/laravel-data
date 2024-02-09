<?php

namespace Spatie\LaravelData\Support\Creation;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\LengthAwarePaginator;

class CollectableMetaData
{
    public function __construct(
        public ?int $paginator_total = null,
        public ?int $paginator_page = null,
        public ?int $paginator_per_page = null,
        public ?Cursor $paginator_cursor = null,
    ) {
    }

    public static function fromOther(
        mixed $items,
    ): self {
        if ($items instanceof LengthAwarePaginator) {
            return new self(
                paginator_total: $items->total(),
                paginator_page: $items->currentPage(),
                paginator_per_page: $items->perPage(),
            );
        }

        if ($items instanceof Paginator || $items instanceof AbstractPaginator) {
            return new self(
                paginator_page: $items->currentPage(),
                paginator_per_page: $items->perPage()
            );
        }

        if ($items instanceof CursorPaginator || $items instanceof AbstractCursorPaginator) {
            return new self(
                paginator_per_page: $items->perPage(),
                paginator_cursor: $items->cursor()
            );
        }

        return new self();
    }
}
