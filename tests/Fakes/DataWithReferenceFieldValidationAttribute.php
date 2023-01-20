<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\Validation\RequiredIf;
use Spatie\LaravelData\Data;

class DataWithReferenceFieldValidationAttribute extends Data
{
    public bool $check_string;

    #[RequiredIf('check_string', true)]
    public string $string;
}
