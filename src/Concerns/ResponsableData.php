<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Support\TransformationType;

/** @mixin \Spatie\LaravelData\Data|\Spatie\LaravelData\DataCollection */
trait ResponsableData
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        if ($request->has('include')) {
            $this->include(...explode(',', $request->get('include')));
        }

        if ($request->has('exclude')) {
            $this->exclude(...explode(',', $request->get('exclude')));
        }

        return new JsonResponse($this->transform(TransformationType::request()));
    }

    public function allowedRequestIncludes(): ?array
    {
        return null;
    }

    public function allowedRequestExcludes(): ?array
    {
        return null;
    }
}
