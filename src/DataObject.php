<?php

namespace Spatie\LaravelData;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Castable as EloquentCastable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use Illuminate\Validation\Validator;
use JsonSerializable;
use Spatie\LaravelData\Support\PartialTrees;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

interface DataObject extends Arrayable, Responsable, Jsonable, EloquentCastable, JsonSerializable
{
    // AppendableData

    public function with(): array;

    public function additional(array $additional): static;

    public function getAdditionalData(): array;

    // BaseData

    public static function optional(mixed ...$payloads): ?DataObject;

    public static function from(mixed ...$payloads): DataObject;

    public static function collection(Enumerable|array|AbstractPaginator|AbstractCursorPaginator|Paginator|DataCollection $items): DataCollection|PaginatedDataCollection;

    public static function normalizers(): array;

    public static function pipeline(): DataPipeline;

    public static function empty(array $extra = []): array;

    public function transform(
        bool $transformValues = true,
        WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
    ): array;

    public static function castUsing(array $arguments);

    // IncludeableData

    public function withPartialTrees(
        PartialTrees $partialTrees,
    ): static;

    public function include(string ...$includes): static;

    public function exclude(string ...$excludes): static;

    public function only(string ...$only): static;

    public function except(string ...$except): static;

    public function onlyWhen(string $only, bool|Closure $condition): static;

    public function exceptWhen(string $except, bool|Closure $condition): self;

    public function getPartialTrees(): PartialTrees;

    // ResponsableData

    public static function allowedRequestIncludes(): ?array;

    public static function allowedRequestExcludes(): ?array;

    public static function allowedRequestOnly(): ?array;

    public static function allowedRequestExcept(): ?array;

    // TransformableData

    public function all(): array;

    public function toArray(): array;

    public function toJson($options = 0): string;

    public function jsonSerialize(): array;

    // ValidateableData

    public static function validate(Arrayable|array $payload): Arrayable|array;

    public static function validateAndCreate(Arrayable|array $payload): DataObject;

    public static function withValidator(Validator $validator): void;

    // WrappableData

    public function withoutWrapping(): static;

    public function wrap(string $key): static;

    public function getWrap(): Wrap;
}
