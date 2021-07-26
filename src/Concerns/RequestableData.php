<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Http\Request;

trait RequestableData
{
    public static function createFromRequest(Request $request): static
    {
        return static::createFromArray($request->all());
    }

    public static function rules(): array
    {
        return [];
    }
}
