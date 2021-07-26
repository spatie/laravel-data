<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Http\Request;
use Spatie\LaravelData\Actions\ResolveDataObjectFromArrayAction;

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
