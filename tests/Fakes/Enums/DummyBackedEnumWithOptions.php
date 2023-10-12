<?php

namespace Spatie\LaravelData\Tests\Fakes\Enums;

use Spatie\LaravelData\Tests\Fakes\Interfaces\HasOptions;

enum DummyBackedEnumWithOptions: string implements HasOptions
{
    case CAPTAIN = 'captain';
    case FIRST_OFFICER = 'first_officer';

    public function label(): string
    {
        return match ($this) {
            self::CAPTAIN => 'Captain',
            self::FIRST_OFFICER => 'First Officer',
        };
    }

    public function toOptions(): array
    {
        return array_map(
            callback: fn (self $enum) => [
                'value' => $enum->value,
                'label' => $enum->label(),
            ],
            array: self::cases()
        );

    }
}
