<?php

namespace Spatie\LaravelData\Support\Wrapping;

enum WrapType
{
    case UseGlobal;
    case Disabled;
    case Defined;
}
