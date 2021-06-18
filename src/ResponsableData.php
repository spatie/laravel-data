<?php

namespace Spatie\LaravelData;

use Illuminate\Http\JsonResponse;

/** @mixin \Spatie\LaravelData\Data|\Spatie\LaravelData\DataCollection|\Spatie\LaravelData\PaginatedDataCollection */
trait ResponsableData
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        if ($request->has('includes')) {
            $this->include(...explode(',', $request->get('includes')));
        }

        if ($request->has('excludes')) {
            $this->exclude(...explode(',', $request->get('excludes')));
        }

        return new JsonResponse($this->toArray());
    }
}
