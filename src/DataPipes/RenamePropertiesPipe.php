<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;

class RenamePropertiesPipe extends DataPipe
{
    public function execute(mixed $value, Collection $payload, DataClass $class): Collection
    {
        $replacements = [
            'a' => 'b',
        ];

        foreach ($replacements as $previous => $new) {
            $payload->has($previous);
            $payload->put($new, $payload->get($previous));
            $payload->forget($previous);
        }

        return $payload;
    }
}
