<?php

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Resource;

it('can compare data objects with equal property values', function (string $dataClass1, string $dataClass2) {
    // Basic comparison with scalar properties
    $data1 = new $dataClass1(id: 10);
    $data2 = $dataClass2::from($data1);

    // Default PHP object comparison (checks reference equality)
    expect($data1 == $data2)->toBeFalse();

    // Custom equalTo comparator (checks property value equality)
    expect($data1->equalTo($data2))->toBeTrue();
    expect($data2->equalTo($data1))->toBeTrue();

    // Verifies that equalTo returns false when values differ
    $data2->name = 'a name 2';
    expect($data1->equalTo($data2))->toBeFalse();
    expect($data2->equalTo($data1))->toBeFalse();
})->with([
    'from same class' => [DataWithOptionalAttribute::class, DataWithOptionalAttribute::class],
    'from different classes' => [DataWithOptionalAttribute::class, ResourceWithOptionalAttribute::class],
    'from different classes (reversed)' => [ResourceWithOptionalAttribute::class, DataWithOptionalAttribute::class],
    'from same class (reversed)' => [ResourceWithOptionalAttribute::class, ResourceWithOptionalAttribute::class],
]);

class DataWithOptionalAttribute extends Data
{
    public function __construct(
        public int $id,
        public string|Optional $name = new Optional,
    ) {}
}

class ResourceWithOptionalAttribute extends Resource
{
    public function __construct(
        public int $id,
        public string|Optional $name = new Optional,
    ) {}
}
