<?php

namespace Spatie\LaravelData\Support\Partials;

use Closure;

trait ForwardsToPartialsDefinition
{
    /**
     * @return object{
     *     includePartials: ?PartialsCollection,
     *     excludePartials: ?PartialsCollection,
     *     onlyPartials: ?PartialsCollection,
     *     exceptPartials: ?PartialsCollection,
     * }
     */
    abstract protected function getPartialsContainer(): object;

    public function include(string ...$includes): static
    {
        $partialsCollection = $this->getPartialsContainer()->includePartials ??= new PartialsCollection();

        foreach ($includes as $include) {
            $partialsCollection->attach(Partial::create($include));
        }

        return $this;
    }

    public function includePermanently(string ...$includes): static
    {
        $partialsCollection = $this->getPartialsContainer()->includePartials ??= new PartialsCollection();

        foreach ($includes as $include) {
            $partialsCollection->attach(Partial::create($include, permanent: true));
        }

        return $this;
    }

    public function exclude(string ...$excludes): static
    {
        $partialsCollection = $this->getPartialsContainer()->excludePartials ??= new PartialsCollection();

        foreach ($excludes as $exclude) {
            $partialsCollection->attach(Partial::create($exclude));
        }

        return $this;
    }

    public function excludePermanently(string ...$excludes): static
    {
        $partialsCollection = $this->getPartialsContainer()->excludePartials ??= new PartialsCollection();

        foreach ($excludes as $exclude) {
            $partialsCollection->attach(Partial::create($exclude, permanent: true));
        }

        return $this;
    }

    public function only(string ...$only): static
    {
        $partialsCollection = $this->getPartialsContainer()->onlyPartials ??= new PartialsCollection();

        foreach ($only as $onlyDefinition) {
            $partialsCollection->attach(Partial::create($onlyDefinition));
        }

        return $this;
    }

    public function onlyPermanently(string ...$only): static
    {
        $partialsCollection = $this->getPartialsContainer()->onlyPartials ??= new PartialsCollection();

        foreach ($only as $onlyDefinition) {
            $partialsCollection->attach(Partial::create($onlyDefinition, permanent: true));
        }

        return $this;
    }

    public function except(string ...$except): static
    {
        $partialsCollection = $this->getPartialsContainer()->exceptPartials ??= new PartialsCollection();

        foreach ($except as $exceptDefinition) {
            $partialsCollection->attach(Partial::create($exceptDefinition));
        }

        return $this;
    }

    public function exceptPermanently(string ...$except): static
    {
        $partialsCollection = $this->getPartialsContainer()->exceptPartials ??= new PartialsCollection();

        foreach ($except as $exceptDefinition) {
            $partialsCollection->attach(Partial::create($exceptDefinition, permanent: true));
        }

        return $this;
    }

    public function includeWhen(string $include, bool|Closure $condition, bool $permanent = false): static
    {
        $partialsCollection = $this->getPartialsContainer()->includePartials ??= new PartialsCollection();

        if (is_callable($condition)) {
            $partialsCollection->attach(Partial::createConditional($include, $condition, permanent: $permanent));
        } elseif ($condition === true) {
            $partialsCollection->attach(Partial::create($include, permanent: $permanent));
        }

        return $this;
    }

    public function excludeWhen(string $exclude, bool|Closure $condition, bool $permanent = false): static
    {
        $partialsCollection = $this->getPartialsContainer()->excludePartials ??= new PartialsCollection();

        if (is_callable($condition)) {
            $partialsCollection->attach(Partial::createConditional($exclude, $condition, permanent: $permanent));
        } elseif ($condition === true) {
            $partialsCollection->attach(Partial::create($exclude, permanent: $permanent));
        }

        return $this;
    }

    public function onlyWhen(string $only, bool|Closure $condition, bool $permanent = false): static
    {
        $partialsCollection = $this->getPartialsContainer()->onlyPartials ??= new PartialsCollection();

        if (is_callable($condition)) {
            $partialsCollection->attach(Partial::createConditional($only, $condition, permanent: $permanent));
        } elseif ($condition === true) {
            $partialsCollection->attach(Partial::create($only, permanent: $permanent));
        }

        return $this;
    }

    public function exceptWhen(string $except, bool|Closure $condition, bool $permanent = false): static
    {
        $partialsCollection = $this->getPartialsContainer()->exceptPartials ??= new PartialsCollection();

        if (is_callable($condition)) {
            $partialsCollection->attach(Partial::createConditional($except, $condition, permanent: $permanent));
        } elseif ($condition === true) {
            $partialsCollection->attach(Partial::create($except, permanent: $permanent));
        }

        return $this;
    }
}
