<?php

namespace Spatie\LaravelData;

use Spatie\LaravelData\Concerns\AppendableData;
use Spatie\LaravelData\Concerns\BaseData;
use Spatie\LaravelData\Concerns\ContextableData;
use Spatie\LaravelData\Concerns\EmptyData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\WrappableData;
use Spatie\LaravelData\Contracts\AppendableData as AppendableDataContract;
use Spatie\LaravelData\Contracts\BaseData as BaseDataContract;
use Spatie\LaravelData\Contracts\EmptyData as EmptyDataContract;
use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Contracts\ResponsableData as ResponsableDataContract;
use Spatie\LaravelData\Contracts\TransformableData as TransformableDataContract;
use Spatie\LaravelData\Contracts\WrappableData as WrappableDataContract;
use Spatie\LaravelData\DataPipes\AuthorizedDataPipe;
use Spatie\LaravelData\DataPipes\CastPropertiesDataPipe;
use Spatie\LaravelData\DataPipes\DefaultValuesDataPipe;
use Spatie\LaravelData\DataPipes\FillRouteParameterPropertiesDataPipe;
use Spatie\LaravelData\DataPipes\MapPropertiesDataPipe;
use Spatie\LaravelData\DataPipes\ValidatePropertiesDataPipe;

class Resource implements BaseDataContract, AppendableDataContract, IncludeableDataContract, ResponsableDataContract, TransformableDataContract, WrappableDataContract, EmptyDataContract
{
    use BaseData;
    use AppendableData;
    use IncludeableData;
    use ResponsableData;
    use TransformableData;
    use WrappableData;
    use EmptyData;
    use ContextableData;

    public static function pipeline(): DataPipeline
    {
        return DataPipeline::create()
            ->into(static::class)
            ->through(MapPropertiesDataPipe::class)
            ->through(FillRouteParameterPropertiesDataPipe::class)
            ->through(DefaultValuesDataPipe::class)
            ->through(CastPropertiesDataPipe::class);
    }
}
