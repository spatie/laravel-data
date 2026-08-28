<?php

use Spatie\LaravelData\Support\Creation\ConstructionState;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

function makeConstructionState(): ConstructionState
{
    return new ConstructionState(
        CreationContextFactory::createFromConfig(SimpleData::class)->get(),
        SimpleData::class,
    );
}

it('writes payload values at the root', function () {
    $state = makeConstructionState();

    $state->writePayload('title', 'Hello');

    expect($state->payload())->toBe(['title' => 'Hello']);
});

it('writes payload values for nested properties under their source keys', function () {
    $state = makeConstructionState();

    $state->writePayload('title', 'Hello');
    $state->enterProperty('author', 'writer');
    $state->writePayload('name', 'Ruben');
    $state->leave();

    expect($state->payload())->toBe([
        'title' => 'Hello',
        'writer' => ['name' => 'Ruben'],
    ]);
});

it('writes payload values inside collection indices', function () {
    $state = makeConstructionState();

    $state->enterProperty('posts');
    $state->enterIndex(0);
    $state->writePayload('title', 'First');
    $state->leave();
    $state->enterIndex(1);
    $state->writePayload('title', 'Second');
    $state->leave();
    $state->leave();

    expect($state->payload())->toBe([
        'posts' => [
            0 => ['title' => 'First'],
            1 => ['title' => 'Second'],
        ],
    ]);
});

it('reads and checks payload values at the current path', function () {
    $state = makeConstructionState();

    $state->enterProperty('author', 'writer');
    $state->writePayload('name', 'Ruben');

    expect($state->hasPayload('name'))->toBeTrue()
        ->and($state->getPayload('name'))->toBe('Ruben')
        ->and($state->hasPayload('missing'))->toBeFalse()
        ->and($state->getPayload('missing'))->toBeNull();

    $state->leave();

    expect($state->hasPayload('name'))->toBeFalse();
});

it('builds dot paths from payload segments', function () {
    $state = makeConstructionState();

    expect($state->dotPath('title'))->toBe('title');

    $state->enterProperty('author', 'writer');

    expect($state->dotPath())->toBe('writer')
        ->and($state->dotPath('name'))->toBe('writer.name');

    $state->leave();
    $state->enterProperty('posts');
    $state->enterIndex(0);

    expect($state->dotPath('title'))->toBe('posts.0.title')
        ->and($state->depth())->toBe(2);
});
