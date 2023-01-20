<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class DummyDataWithContextOverwrittenValidationRules extends Data
{
    public string $string;

    #[Required]
    public bool $validate_as_email;

    public static function rules(ValidationContext $context): array
    {
        return $context->payload['validate_as_email'] ?? false
            ? ['string' => ['required', 'string', 'email']]
            : [];
    }
}
