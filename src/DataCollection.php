<?php

namespace Spatie\LaravelData;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use Illuminate\Contracts\Database\Eloquent\Castable as EloquentCastable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use IteratorAggregate;
use JsonSerializable;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\WrapableData;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Exceptions\InvalidDataCollectionOperation;
use Spatie\LaravelData\Support\EloquentCasts\DataCollectionEloquentCast;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\DataCollectionTransformer;

/**
 * @template TValue
 *
 * @implements \ArrayAccess<array-key, TValue>
 * @implements  \Illuminate\Contracts\Support\Arrayable<array-key, TValue>
 * @implements  \IteratorAggregate<array-key, TValue>
 */
class DataCollection implements Responsable, Arrayable, Jsonable, JsonSerializable, IteratorAggregate, Countable, ArrayAccess, EloquentCastable
{
    use ResponsableData;
    use IncludeableData;
    use WrapableData;
    use TransformableData;

    private ?Closure $through = null;

    private ?Closure $filter = null;

    /** @var Enumerable<array-key, TValue> */
    private Enumerable $items;

    /**
     * @param class-string<TValue> $dataClass
     * @param array|Enumerable<array-key, TValue>|DataCollection $items
     */
    public function __construct(
        public readonly string $dataClass,
        Enumerable|array|DataCollection $items
    ) {
        if (is_array($items)) {
            $items = new Collection($items);
        }

        if ($items instanceof DataCollection) {
            $items = $items->toCollection();
        }

        $this->items = $items->map(
            fn ($item) => $item instanceof $this->dataClass ? $item : $this->dataClass::from($item)
        );
    }

    /**
     * @param Closure(TValue, array-key): TValue $through
     *
     * @return static
     */
    public function through(Closure $through): static
    {
        $this->through = $through;

        return $this;
    }

    /**
     * @param Closure(TValue, array-key): bool $filter
     *
     * @return static
     */
    public function filter(Closure $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return array<array-key, TValue>
     */
    public function items(): array
    {
        return $this->items->all();
    }

    /**
     * @return array<array>
     */
    public function transform(
        bool $transformValues = true,
        WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
    ): array {
        $transformer = new DataCollectionTransformer(
            $this->dataClass,
            $transformValues,
            $wrapExecutionType,
            $this->getPartialTrees(),
            $this->items,
            $this->through,
            $this->filter,
            $this->getWrap(),
        );

        return $transformer->transform();
    }

    /** @return \Illuminate\Support\Enumerable<array-key, TValue> */
    public function toCollection(): Enumerable
    {
        return $this->items;
    }

    /**  @return \ArrayIterator<array-key, array> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->transform(
            transformValues: false,
        ));
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param array-key $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        if (! $this->items instanceof ArrayAccess) {
            throw InvalidDataCollectionOperation::create();
        }

        return $this->items->offsetExists($offset);
    }

    /**
     * @param array-key $offset
     *
     * @return TValue
     */
    public function offsetGet($offset): mixed
    {
        if (! $this->items instanceof ArrayAccess) {
            throw InvalidDataCollectionOperation::create();
        }

        return $this->items->offsetGet($offset);
    }

    /**
     * @param array-key|null $offset
     * @param TValue $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (! $this->items instanceof ArrayAccess) {
            throw InvalidDataCollectionOperation::create();
        }

        $value = $value instanceof DataObject
            ? $value
            : $this->dataClass::from($value);

        $this->items->offsetSet($offset, $value);
    }

    /**
     * @param array-key $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        if (! $this->items instanceof ArrayAccess) {
            throw InvalidDataCollectionOperation::create();
        }

        $this->items->offsetUnset($offset);
    }

    public static function castUsing(array $arguments)
    {
        if (count($arguments) !== 1) {
            throw CannotCastData::dataCollectionTypeRequired();
        }

        return new DataCollectionEloquentCast(current($arguments));
    }
}
