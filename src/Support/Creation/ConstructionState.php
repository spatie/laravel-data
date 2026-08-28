<?php

namespace Spatie\LaravelData\Support\Creation;

class ConstructionState
{
    protected array $payload = [];

    protected array $structure;

    /** @var array<int, array{payloadKey: string|int, structureKey: ?string, isIndex: bool}> */
    protected array $path = [];

    private function __construct(
        public readonly CreationContext $creationContext,
        string $class,
    ) {
        $this->structure = [
            'class' => $class,
            'mappings' => [],
            'children' => [],
        ];
    }

    public static function create(CreationContext $creationContext, string $class): self
    {
        return new self($creationContext, $class);
    }

    public function enterProperty(string $property, ?string $mappedKey = null): void
    {
        $this->path[] = [
            'payloadKey' => $mappedKey ?? $property,
            'structureKey' => $property,
            'isIndex' => false,
        ];
    }

    public function enterItem(string|int $index): void
    {
        $this->path[] = [
            'payloadKey' => $index,
            'structureKey' => null,
            'isIndex' => true,
        ];
    }

    public function leave(): void
    {
        array_pop($this->path);
    }

    public function depth(): int
    {
        return count($this->path);
    }

    public function dotPath(string|int|null $key = null): string
    {
        $segments = array_map(
            fn (array $segment) => $segment['payloadKey'],
            $this->path
        );

        if ($key !== null) {
            $segments[] = $key;
        }

        return implode('.', $segments);
    }

    public function writeValue(string|int $key, mixed $value): void
    {
        $slot = &$this->payload;

        foreach ($this->path as $segment) {
            $pathKey = $segment['payloadKey'];

            if (! array_key_exists($pathKey, $slot) || ! is_array($slot[$pathKey])) {
                $slot[$pathKey] = [];
            }

            $slot = &$slot[$pathKey];
        }

        $slot[$key] = $value;
    }

    public function hasValue(string|int $key): bool
    {
        $slot = $this->payloadAtCurrentPath();

        return is_array($slot) && array_key_exists($key, $slot);
    }

    public function getValue(string|int $key): mixed
    {
        $slot = $this->payloadAtCurrentPath();

        if (! is_array($slot) || ! array_key_exists($key, $slot)) {
            return null;
        }

        return $slot[$key];
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function currentValues(): array
    {
        $slot = $this->payloadAtCurrentPath();

        if (! is_array($slot)) {
            return [];
        }

        return $slot;
    }

    public function recordMapping(string $property, string $mappedKey): void
    {
        $node = &$this->ensureStructureNodeAtCurrentPath();

        $node['mappings'][$property] = $mappedKey;
    }

    public function originalKey(string $property): string
    {
        $node = $this->structureNodeAtCurrentPath();

        if ($node === null) {
            return $property;
        }

        return $node['mappings'][$property] ?? $property;
    }

    public function setNodeClass(string $class): void
    {
        $itemIndex = $this->currentItemIndex();

        $node = &$this->ensureStructureNodeAtCurrentPath();

        if ($itemIndex !== null) {
            if ($node['class'] === $class) {
                return;
            }

            $node['indexClasses'][$itemIndex] = $class;

            return;
        }

        $node['class'] = $class;
    }

    public function nodeClass(): ?string
    {
        $node = $this->structureNodeAtCurrentPath();

        if ($node === null) {
            return null;
        }

        $itemIndex = $this->currentItemIndex();

        if ($itemIndex !== null) {
            return $node['indexClasses'][$itemIndex] ?? $node['class'];
        }

        return $node['class'];
    }

    protected function currentItemIndex(): string|int|null
    {
        $last = $this->path[count($this->path) - 1] ?? null;

        if ($last === null || $last['isIndex'] === false) {
            return null;
        }

        return $last['payloadKey'];
    }

    public function structure(): array
    {
        return $this->structure;
    }

    protected function payloadAtCurrentPath(): mixed
    {
        $slot = $this->payload;

        foreach ($this->path as $segment) {
            $key = $segment['payloadKey'];

            if (! is_array($slot) || ! array_key_exists($key, $slot)) {
                return null;
            }

            $slot = $slot[$key];
        }

        return $slot;
    }

    protected function structureNodeAtCurrentPath(): ?array
    {
        $node = $this->structure;

        foreach ($this->path as $segment) {
            if ($segment['isIndex']) {
                continue;
            }

            $key = $segment['structureKey'];

            if (! array_key_exists($key, $node['children'])) {
                return null;
            }

            $node = $node['children'][$key];
        }

        return $node;
    }

    protected function &ensureStructureNodeAtCurrentPath(): array
    {
        $node = &$this->structure;

        foreach ($this->path as $segment) {
            if ($segment['isIndex']) {
                continue;
            }

            $key = $segment['structureKey'];

            if (! array_key_exists($key, $node['children'])) {
                $node['children'][$key] = [
                    'class' => null,
                    'mappings' => [],
                    'children' => [],
                ];
            }

            $node = &$node['children'][$key];
        }

        return $node;
    }
}
