<?php

namespace Spatie\LaravelData;

use Illuminate\Http\Request;

interface RequestData
{
    public static function createFromRequest(Request $request);

    public static function rules(): array;

    public static function casts() : array;
}
