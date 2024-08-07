<?php

namespace Spatie\LaravelData\Resolvers;

use Spatie\LaravelData\Support\DataConfig;

class CastPropertyResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
    ) {
    }


}
