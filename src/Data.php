<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\Concerns\AppendableData;
use Spatie\LaravelData\Concerns\BaseData;
use Spatie\LaravelData\Concerns\ContextableData;
use Spatie\LaravelData\Concerns\DataTrait;
use Spatie\LaravelData\Concerns\DefaultableData;
use Spatie\LaravelData\Concerns\EmptyData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\ValidateableData;
use Spatie\LaravelData\Concerns\WrappableData;
use Spatie\LaravelData\Contracts\DataObject;

abstract class Data implements DataObject
{
    use ResponsableData;
    use IncludeableData;
    use AppendableData;
    use ValidateableData;
    use WrappableData;
    use TransformableData;
    use BaseData;
    use EmptyData;
    use ContextableData;
    use DefaultableData;
}
