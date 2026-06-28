<?php

declare(strict_types=1);

namespace Spatie\LaravelData\Tests\Stubs;

use Spatie\LaravelData\Data;

final class RecordLabel extends Data
{
    public readonly string $name;
    public readonly string $country;
}
