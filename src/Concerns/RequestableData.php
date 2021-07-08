<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Http\Request;

trait RequestableData
{
    public static function createFromRequest(Request $request): ?static
    {
        return null;
    }

    public static function getRules(): array
    {
        return [];
    }
}
