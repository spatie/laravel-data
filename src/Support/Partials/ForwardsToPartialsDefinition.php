<?php

namespace Spatie\LaravelData\Support\Partials;

use Closure;
use SplObjectStorage;

trait ForwardsToPartialsDefinition
{
    /**
     * @return object{
     *     includePartials: SplObjectStorage<Partial>,
     *     excludePartials: SplObjectStorage<Partial>,
     *     onlyPartials: SplObjectStorage<Partial>,
     *     exceptPartials: SplObjectStorage<Partial>,
     * }
     */
    abstract protected function getPartialsContainer(): object;

    public function include(string ...$includes): static
    {
        foreach ($includes as $include) {
            $this->getPartialsContainer()->includePartials->attach(Partial::create($include));
        }

        return $this;
    }

    public function exclude(string ...$excludes): static
    {
        foreach ($excludes as $exclude) {
            $this->getPartialsContainer()->excludePartials->attach(Partial::create($exclude));
        }

        return $this;
    }

    public function only(string ...$only): static
    {
        foreach ($only as $onlyDefinition) {
            $this->getPartialsContainer()->onlyPartials->attach(Partial::create($onlyDefinition));
        }

        return $this;
    }

    public function except(string ...$except): static
    {
        foreach ($except as $exceptDefinition) {
            $this->getPartialsContainer()->exceptPartials->attach(Partial::create($exceptDefinition));
        }

        return $this;
    }

    public function includeWhen(string $include, bool|Closure $condition): static
    {
        if (is_callable($condition)) {
            $this->getPartialsContainer()->includePartials->attach(Partial::createConditional($include, $condition));
        } else if ($condition === true) {
            $this->getPartialsContainer()->includePartials->attach(Partial::create($include));
        }

        return $this;
    }

    public function excludeWhen(string $exclude, bool|Closure $condition): static
    {
        if (is_callable($condition)) {
            $this->getPartialsContainer()->excludePartials->attach(Partial::createConditional($exclude, $condition));
        } else if ($condition === true) {
            $this->getPartialsContainer()->excludePartials->attach(Partial::create($exclude));
        }

        return $this;
    }

    public function onlyWhen(string $only, bool|Closure $condition): static
    {
        if (is_callable($condition)) {
            $this->getPartialsContainer()->onlyPartials->attach(Partial::createConditional($only, $condition));
        } else if ($condition === true) {
            $this->getPartialsContainer()->onlyPartials->attach(Partial::create($only));
        }

        return $this;
    }

    public function exceptWhen(string $except, bool|Closure $condition): static
    {
        if (is_callable($condition)) {
            $this->getPartialsContainer()->exceptPartials->attach(Partial::createConditional($except, $condition));
        } else if ($condition === true) {
            $this->getPartialsContainer()->exceptPartials->attach(Partial::create($except));
        }

        return $this;
    }
}
