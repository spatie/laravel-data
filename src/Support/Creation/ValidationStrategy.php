<?php

namespace Spatie\LaravelData\Support\Creation;

enum ValidationStrategy: string
{
    case Always = 'always';
    case OnlyRequests = 'only_requests';
    case Disabled = 'disabled';
    /** @internal  */
    case AlreadyRan = 'already_ran';
}
