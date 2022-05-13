<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Http\JsonResponse;
use Spatie\LaravelData\Resolvers\PartialsTreeFromRequestResolver;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapType;
use function _PHPStan_8862d57cc\React\Promise\reject;

trait WrapableData
{
    protected null|Wrap $wrap = null;

    public function withoutWrapping(): static
    {
        $this->wrap = new Wrap(WrapType::Disabled);

        return $this;
    }

    public function wrap(string $key): static
    {
        $this->wrap = new Wrap(WrapType::Defined, $key);

        return $this;
    }

    public function getWrap(): Wrap
    {
        if ($this->wrap) {
            return $this->wrap;
        }

        if (method_exists($this, 'defaultWrap')) {
            return new Wrap(WrapType::Defined, $this->defaultWrap());
        }

        return $this->wrap ?? new Wrap(WrapType::UseGlobal);
    }
}
