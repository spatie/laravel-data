<?php

namespace Spatie\LaravelData;

use Illuminate\Http\Request;

interface RequestData
{
    public static function rules(): array;

    public static function createFromRequest(Request $request): static;
}
