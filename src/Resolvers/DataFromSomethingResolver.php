<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Normalizers\ArrayableNormalizer;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataMethod;

class DataFromSomethingResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected DataFromArrayResolver $dataFromArrayResolver,
        protected bool $withoutMagicalCreation = false,
        protected array $ignoredMagicalMethods = [],
    ) {
    }

    public function withoutMagicalCreation(bool $withoutMagicalCreation = true): self
    {
        $this->withoutMagicalCreation = $withoutMagicalCreation;

        return $this;
    }

    public function ignoreMagicalMethods(string ...$methods): self
    {
        array_push($this->ignoredMagicalMethods, ...$methods);

        return $this;
    }

    public function execute(string $class, mixed ...$payloads): BaseData
    {
        if ($data = $this->createFromCustomCreationMethod($class, $payloads)) {
            return $data;
        }

        $properties = array_reduce(
            $payloads,
            function (Collection $carry, mixed $payload) use ($class) {
                /** @var BaseData $class */
                $pipeline = $class::pipeline();

                foreach ($class::normalizers() as $normalizer) {
                    $pipeline->normalizer($normalizer);
                }

                return $carry->merge($pipeline->using($payload)->execute());
            },
            collect(),
        );

        return $this->dataFromArrayResolver->execute($class, $properties);
    }

    protected function createFromCustomCreationMethod(string $class, array $payloads): ?BaseData
    {
        if ($this->withoutMagicalCreation) {
            return null;
        }

        /** @var Collection<\Spatie\LaravelData\Support\DataMethod> $customCreationMethods */
        $customCreationMethods = $this->dataConfig
            ->getDataClass($class)
            ->methods
            ->filter(
                fn (DataMethod $method) => $method->isCustomCreationMethod
                && ! in_array($method->name, $this->ignoredMagicalMethods)
            );

        $methodName = null;

        foreach ($customCreationMethods as $customCreationMethod) {
            if ($customCreationMethod->accepts(...$payloads)) {
                $methodName = $customCreationMethod->name;

                break;
            }
        }

        if ($methodName === null) {
            return null;
        }

        foreach ($payloads as $payload) {
            if ($payload instanceof Request) {
                $class::pipeline()
                    ->normalizer(ArrayableNormalizer::class)
                    ->into($class)
                    ->using($payload)
                    ->execute();
            }
        }

        return $class::$methodName(...$payloads);
    }
}
