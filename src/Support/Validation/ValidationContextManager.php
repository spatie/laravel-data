<?php

namespace Spatie\LaravelData\Support\Validation;

class ValidationContextManager
{
    protected ?string $currentContext = null;

    public function setContext(?string $context): static
    {
        $this->currentContext = $context;

        return $this;
    }

    public function getContext(): ?string
    {
        return $this->currentContext;
    }

    public function clearContext(): static
    {
        $this->currentContext = null;

        return $this;
    }

    public function hasContext(): bool
    {
        return $this->currentContext !== null;
    }
}
