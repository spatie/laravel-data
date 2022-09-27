<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Data;

class SimpleDataWithExplicitValidationRuleAttributeData extends Data
{
    public function __construct(
        #[Email]
        public string $email,
    ) {
    }
}
