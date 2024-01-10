<?php

namespace Spatie\LaravelData\Support\Creation;

enum ValidationType: string
{
    case Always = 'always';
    case OnlyRequests = 'only_requests';
    case Disabled = 'disabled';
}
