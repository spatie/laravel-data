<?php

namespace Spatie\LaravelData\Contracts;

use Closure;
use Spatie\LaravelData\Support\PartialTrees;

interface IncludeableData
{
    public function withPartialTrees(PartialTrees $partialTrees): IncludeableData;

    public function include(string ...$includes): IncludeableData;

    public function exclude(string ...$excludes): IncludeableData;

    public function only(string ...$only): IncludeableData;

    public function except(string ...$except): IncludeableData;

    public function onlyWhen(string $only, bool|Closure $condition): IncludeableData;

    public function exceptWhen(string $except, bool|Closure $condition): IncludeableData;

    public function getPartialTrees(): PartialTrees;
}
