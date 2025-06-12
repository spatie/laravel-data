<?php

use Spatie\LaravelData\Data;

it('calls jsonSerialize when toResponse is invoked', function () {

    $data = new class extends Data
    {
        public function jsonSerialize(): array
        {
            return [
                'string' => 'Hello from serialize',
            ];
        }
    };

    expect($data->toResponse(request())->getContent())->toBe('{"string":"Hello from serialize"}');
});
