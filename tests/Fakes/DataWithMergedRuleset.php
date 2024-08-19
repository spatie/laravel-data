<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\MergeRuleset;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

#[MergeRuleset]
class DataWithMergedRuleset extends Data
{
    public function __construct(
        #[Max(10)]
        public string $first_name,
    ) {
    }

    public static function rules(): array
    {
        return [
            'first_name' => ['min:2']
        ];
    }
}
