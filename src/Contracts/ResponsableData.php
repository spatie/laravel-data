<?php

namespace Spatie\LaravelData\Contracts;

use Illuminate\Contracts\Support\Responsable;

interface ResponsableData extends TransformableData, Responsable
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request);

    public static function allowedRequestIncludes(): ?array;

    public static function allowedRequestExcludes(): ?array;

    public static function allowedRequestOnly(): ?array;

    public static function allowedRequestExcept(): ?array;
}
