<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Illuminate\Contracts\Auth\Authenticatable;

class FakeAuthenticatable implements Authenticatable
{
    public string $property = 'value';

    public function getAuthIdentifierName()
    {

    }

    public function getAuthIdentifier()
    {

    }

    public function getAuthPasswordName()
    {

    }

    public function getAuthPassword()
    {

    }

    public function getRememberToken()
    {

    }

    public function setRememberToken($value)
    {

    }

    public function getRememberTokenName()
    {

    }
}
