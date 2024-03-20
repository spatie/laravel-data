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

    public function include(string|array ...$includes): static
    {
        $this->expandPartials(
            $this->getPartialsContainer()->includePartials ??= new PartialsCollection(),
            $includes,
            permanent: false,
        );

        return $this;
    }

    public function includePermanently(string|array ...$includes): static
    {
        $this->expandPartials(
            $this->getPartialsContainer()->includePartials ??= new PartialsCollection(),
            $includes,
            permanent: true,
        );

        return $this;
    }

    public function exclude(string|array ...$excludes): static
    {
        $this->expandPartials(
            $this->getPartialsContainer()->excludePartials ??= new PartialsCollection(),
            $excludes,
            permanent: false,
        );

        return $this;
    }

    public function excludePermanently(string|array ...$excludes): static
    {
        $this->expandPartials(
            $this->getPartialsContainer()->excludePartials ??= new PartialsCollection(),
            $excludes,
            permanent: true,
        );

        return $this;
    }

    public function only(string|array ...$only): static
    {
        $this->expandPartials(
            $this->getPartialsContainer()->onlyPartials ??= new PartialsCollection(),
            $only,
            permanent: false,
        );

        return $this;
    }

    public function onlyPermanently(string|array ...$only): static
    {
        $this->expandPartials(
            $this->getPartialsContainer()->onlyPartials ??= new PartialsCollection(),
            $only,
            permanent: true,
        );

        return $this;
    }

    public function except(string|array ...$except): static
    {
        $this->expandPartials(
            $this->getPartialsContainer()->exceptPartials ??= new PartialsCollection(),
            $except,
            permanent: false,
        );

        return $this;
    }

    public function exceptPermanently(string|array ...$except): static
    {
        $this->expandPartials(
            $this->getPartialsContainer()->exceptPartials ??= new PartialsCollection(),
            $except,
            permanent: true,
        );

        return $this;
    }

    /**
     * @param array<int, string|array<int, string>|array<string,Closure|bool>> $partials
     */
    protected function expandPartials(
        PartialsCollection $partialsCollection,
        array $partials,
        bool $permanent,
    ): void {
        foreach ($partials as $partial) {
            if (is_string($partial)) {
                $partialsCollection->attach(Partial::create($partial, permanent: $permanent));

                continue;
            }

            if (! is_array($partial)) {
                continue;
            }

            foreach ($partial as $key => $subPartial) {
                if (is_string($key) && is_callable($subPartial)) {
                    $partialsCollection->attach(Partial::createConditional($key, condition: $subPartial, permanent: $permanent));

                    continue;
                }

                if (is_string($key) && $subPartial === true) {
                    $partialsCollection->attach(Partial::create($key, permanent: $permanent));

                    continue;
                }

                if (is_string($subPartial)) {
                    $partialsCollection->attach(Partial::create($subPartial, permanent: $permanent));

                    continue;
                }
            }
        }
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
