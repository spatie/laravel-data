<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class AttributeRulesWithStaticFunctionRulesData extends Data
{
    public function __construct(
        #[Min(1)]
        #[Max(5)]
        public readonly array $emails,
    ) {
    }

    public static function rules(): array
    {
        return [
            'emails.*' => ['email'],
        ];
    }
}
