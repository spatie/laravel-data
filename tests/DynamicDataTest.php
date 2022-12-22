<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Concerns\DynamicDataTrait;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Tests\TestSupport\DataValidationAsserter;

enum VehicleType: string
{
    case CAR = 'car';
    case HORSE = 'horse';
}

class Vehicle extends Data
{
    use DynamicDataTrait;

    public VehicleType $type;
    public int $passengers;

    /**
     * Properties that are required to determine the appropriate class
     * @return string[]
     */
    public static function requiredPropertiesForResolving(): array
    {
        return ['type'];
    }

    /**
     * Return the concrete class to use for the given required properties
     * @param array $properties
     * @return string
     */
    public static function dynamicClassName(array $properties): string
    {
        // When validating the enums won't have been cast yet
        if (! $properties['type'] instanceof VehicleType) {
            $properties['type'] = VehicleType::tryFrom($properties['type']);
        }

        return match ($properties['type']) {
            VehicleType::CAR => Car::class,
            VehicleType::HORSE => Horse::class,
            default => static::class,
        };
    }
}

enum Fuel: string
{
    case PETROLEUM = 'petrol';
    case ELECTRIC = 'electric';
}

class Car extends Vehicle
{
    public Fuel $fuel;
    public int $doors;
}

enum Feed: string
{
    case OATS = 'oats';
    case HAY = 'hay';
    case CARROT = 'carrot';
}

class Horse extends Vehicle
{
    public Feed $feed;
}

class TransportCompany extends Data
{
    #[DataCollectionOf(Vehicle::class)]
    public DataCollection $vehicles;
}

it('can handle multiple classes in one data collection', function () {
    $transport = TransportCompany::from([
        'vehicles' => [
            [
                'type' => 'horse',
                'pasengers' => 1,
                'feed' => 'hay',
            ],
            [
                'type' => 'car',
                'passengers' => 5,
                'fuel' => 'electric',
                'doors' => 5,
            ],
        ],
    ]);

    expect($transport->vehicles[0])->toBeInstanceOf(Horse::class);
    expect($transport->vehicles[1])->toBeInstanceOf(Car::class);
});

it('can validate', function () {
    DataValidationAsserter::for(TransportCompany::class)
        ->assertRules([
            'vehicles' => [
                'array',
                'present',
            ],
            'vehicles.*.passengers' => [
                'numeric',
                'required',
            ],
            'vehicles.*.type' => [
                'required',
                new Enum(VehicleType::class),
            ],
        ], [
            'vehicles' => [

            ],
        ])
        ->assertRules([
            'vehicles' => [
                'array',
                'present',
            ],
            'vehicles.*.passengers' => [
                'numeric',
                'required',
            ],
            'vehicles.*.type' => [
                'required',
                new Enum(VehicleType::class),
            ],
            'vehicles.0.feed' => [
                'required',
                new Enum(Feed::class),
            ],
            'vehicles.0.passengers' => [
                'numeric',
                'required',
            ],
            'vehicles.0.type' => [
                'required',
                new Enum(VehicleType::class),
            ],
            'vehicles.1.doors' => [
                'numeric',
                'required',
            ],
            'vehicles.1.fuel' => [
                'required',
                new Enum(Fuel::class),
            ],
            'vehicles.1.passengers' => [
                'numeric',
                'required',
            ],
            'vehicles.1.type' => [
                'required',
                new Enum(VehicleType::class),
            ],
        ], [
            'vehicles' => [
                ['type' => 'horse'],
                ['type' => 'car'],
            ],
        ])
        ->assertErrors([
            'vehicles' => [
                ['type' => 'donkey'],
                ['type' => 'bus'],
            ],
        ], [
            'vehicles.0.type' => [
                'The selected vehicles.0.type is invalid.',
            ],
            'vehicles.1.type' => [
                'The selected vehicles.1.type is invalid.',
            ],
            'vehicles.0.passengers' => [
                'The vehicles.0.passengers field is required.',
            ],
            'vehicles.1.passengers' => [
                'The vehicles.1.passengers field is required.',
            ],
        ]);
});
