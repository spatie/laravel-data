<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(Spatie\LaravelData\Tests\TestCase::class)->in('.');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function onlyPHP81()
{
    if (version_compare(phpversion(), '8.1', '<')) {
        test()->markTestIncomplete('This test is only supported in PHP versions > PHP 8.1');
    }
}
