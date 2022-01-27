<?php

namespace Spatie\LaravelData\DataSerializers;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class RequestSerializer implements DataSerializer
{
    public function serialize(mixed $payload): array|Data|null
    {
        if(! $payload instanceof Request){
            return null;
        }

        return $payload->toArray();
    }
}
