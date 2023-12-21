<?php

namespace Spatie\LaravelData\Support\Caching;

use Spatie\LaravelData\Contracts\BaseData;
use Spatie\StructureDiscoverer\Discover;

class DataClassFinder
{
    public static function fromConfig(array $config): self
    {
        return new self(
            directories: $config['directories'],
            useReflection: $config['reflection_discovery']['enabled'],
            reflectionBasePath: $config['reflection_discovery']['base_path'],
            reflectionRootNamespace: $config['reflection_discovery']['root_namespace'],
        );
    }

    /**
     * @param array<string> $directories
     */
    public function __construct(
        protected array $directories,
        protected bool $useReflection,
        protected ?string $reflectionBasePath,
        protected ?string $reflectionRootNamespace,
    ) {
    }

    public function classes(): array
    {
        $discoverer = Discover::in(...$this->directories)
            ->implementing(BaseData::class);

        if ($this->useReflection) {
            $discoverer->useReflection($this->reflectionBasePath, $this->reflectionRootNamespace);
        }

        return $discoverer->get();
    }
}
