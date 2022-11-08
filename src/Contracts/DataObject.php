<?php

namespace Spatie\LaravelData\Contracts;

use Illuminate\Contracts\Support\Responsable;

interface DataObject extends Responsable, AppendableData, BaseData, TransformableData, IncludeableData, ResponsableData, ValidateableData, WrappableData, PrepareableData
{
}
