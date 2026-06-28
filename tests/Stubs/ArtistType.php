<?php

declare(strict_types=1);

namespace Spatie\LaravelData\Tests\Stubs;

enum ArtistType: string
{
    case Singer = 'singer';
    case Musician = 'musician';
}
