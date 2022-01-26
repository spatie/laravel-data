<?php

namespace Spatie\LaravelData\Console;

class DefaultDataQualifier implements DataQualifier
{
    public function qualify(string $model): string
    {
        $segments = explode('\\', trim($model));
        $baseName = array_pop($segments);
        $segments[] = 'Data';
        $segments[] = $baseName . 'Data';

        return implode('\\', $segments);
    }
}
