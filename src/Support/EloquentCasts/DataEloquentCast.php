<?php

namespace Spatie\LaravelData\Support\EloquentCasts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Support\DataConfig;

/**
 * @template TData of (BaseData&TransformableData)
 *
 * @implements CastsAttributes<TData|null,TData|array|null>
 */
class DataEloquentCast implements CastsAttributes
{
    protected DataConfig $dataConfig;

    /**
     * @param class-string<TData> $dataClass $dataClass
     * @param string[] $arguments
     */
    public function __construct(
        protected string $dataClass,
        protected array $arguments = []
    ) {
        $this->dataConfig = app(DataConfig::class);
    }

    public function get($model, string $key, $value, array $attributes): BaseData|TransformableData|null
    {
        if (is_string($value) && in_array('encrypted', $this->arguments)) {
            $value = Crypt::decryptString($value);
        }

        if (is_null($value) && in_array('default', $this->arguments)) {
            $value = '{}';
        }

        if ($value === null) {
            return null;
        }

        $payload = json_decode($value, true, flags: JSON_THROW_ON_ERROR);

        if ($this->isAbstractClassCast()) {
            /** @var class-string<TData> $dataClass */
            $dataClass = $this->dataConfig->morphMap->getMorphedDataClass($payload['type']) ?? $payload['type'];

            return $dataClass::from($payload['data']);
        }

        return ($this->dataClass)::from($payload);
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $isAbstractClassCast = $this->isAbstractClassCast();

        if (is_array($value) && ! $isAbstractClassCast) {
            $value = ($this->dataClass)::from($value);
        }

        if (! $value instanceof BaseData) {
            throw CannotCastData::shouldBeData($model::class, $key);
        }

        if (! $value instanceof TransformableData) {
            throw CannotCastData::shouldBeTransformableData($model::class, $key);
        }

        $value = $isAbstractClassCast
            ? json_encode([
                'type' => $this->dataConfig->morphMap->getDataClassAlias($value::class) ?? $value::class,
                'data' => json_decode($value->toJson(), associative: true, flags: JSON_THROW_ON_ERROR),
            ])
            : $value->toJson();

        if (in_array('encrypted', $this->arguments)) {
            return Crypt::encryptString($value);
        }

        return $value;
    }

    /**
     * @param TData|null $firstValue
     * @param TData|null $secondValue
     */
    public function compare($model, string $key, $firstValue, $secondValue): bool
    {
        return $this->get($model, $key, $firstValue, [])?->toArray() === $this->get($model, $key, $secondValue, [])?->toArray();
    }

    protected function isAbstractClassCast(): bool
    {
        $dataClass = $this->dataConfig->getDataClass($this->dataClass);

        return $dataClass->isAbstract && ! $dataClass->propertyMorphable;
    }
}
