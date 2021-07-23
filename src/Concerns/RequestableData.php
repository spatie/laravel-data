<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Http\Request;
use Spatie\LaravelData\Actions\ResolveDataObjectFromArrayAction;

trait RequestableData
{
    public static function createFromRequest(Request $request): static
    {
        return app(ResolveDataObjectFromArrayAction::class)->execute(static::class, $request->all());
    }

    public static function rules(): array
    {
        return [];
    }

    public static function casts(): array
    {
        return [];
    }
}
