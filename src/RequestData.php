<?php

namespace Spatie\LaravelData;

use Illuminate\Http\Request;

interface RequestData
{
    public static function createFromRequest(Request $request);

    public static function getRules();
}
