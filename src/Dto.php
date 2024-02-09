<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\Concerns\BaseData;
use Spatie\LaravelData\Concerns\ValidateableData;
use Spatie\LaravelData\Contracts\BaseData as BaseDataContract;
use Spatie\LaravelData\Contracts\ValidateableData as ValidateableDataContract;

class Dto implements ValidateableDataContract, BaseDataContract
{
    use ValidateableData;
    use BaseData;
}
