<?php

declare(strict_types=1);

namespace Spatie\LaravelData\Tests\Stubs;

use Spatie\LaravelData\Data;

final class Contract extends Data
{
    public readonly RecordLabel $label;
    public readonly Person $artist;
}
