<?php

namespace Spatie\LaravelData\Support\Wrapping;

use TypeError;

class Wrap
{
    public function __construct(
        public WrapType $type,
        public null|string $key = null
    ) {
    }

    public function wrap(array $data): array
    {
        $wrapKey = $this->getKey();

        return $wrapKey === null
            ? $data
            : [$wrapKey => $data];
    }

    public function getKey(): null|string
    {
        $globalKey = config('data.wrap');

        return match (true) {
            $this->type === WrapType::Disabled => null,
            $this->type === WrapType::Defined => $this->key,
            $this->type === WrapType::UseGlobal && $globalKey === null => null,
            $this->type === WrapType::UseGlobal && $globalKey => $globalKey,
            default => throw new TypeError('Invalid wrap')
        };
    }
}
