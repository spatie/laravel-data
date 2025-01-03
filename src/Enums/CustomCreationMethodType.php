<?php

namespace Spatie\LaravelData\Enums;

enum CustomCreationMethodType: string
{
    case None = 'None';
    case Object = 'Object';
    case Collection = 'Collection';
}
