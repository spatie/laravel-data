<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Illuminate\Foundation\Auth\User;
use Spatie\LaravelData\Data;

class UserResource extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {
    }

    public static function create(User $user)
    {
        return new self(
            $user->id,
            $user->name,
            $user->email,
        );
    }
}
