<?php

use Spatie\LaravelData\Support\Creation\ConstructionState;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

function makeConstructionState(): ConstructionState
{
    return ConstructionState::create(
        CreationContextFactory::createFromConfig(SimpleData::class)->get(),
        SimpleData::class,
    );
}

it('writes payload values at the root', function () {
    $state = makeConstructionState();

    $state->writeValue('title', 'Hello');

    expect($state->payload())->toBe(['title' => 'Hello']);
});

it('writes payload values for nested properties under their source keys', function () {
    $state = makeConstructionState();

    $state->writeValue('title', 'Hello');
    $state->enterProperty('author', 'writer');
    $state->writeValue('name', 'Ruben');
    $state->leave();

    expect($state->payload())->toBe([
        'title' => 'Hello',
        'writer' => ['name' => 'Ruben'],
    ]);
});

it('writes payload values inside collection indices', function () {
    $state = makeConstructionState();

    $state->enterProperty('posts');
    $state->enterItem(0);
    $state->writeValue('title', 'First');
    $state->leave();
    $state->enterItem(1);
    $state->writeValue('title', 'Second');
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
    $state->writeValue('name', 'Ruben');

    expect($state->hasValue('name'))->toBeTrue()
        ->and($state->getValue('name'))->toBe('Ruben')
        ->and($state->hasValue('missing'))->toBeFalse()
        ->and($state->getValue('missing'))->toBeNull();

    $state->leave();

    expect($state->hasValue('name'))->toBeFalse();
});

it('builds dot paths from payload segments', function () {
    $state = makeConstructionState();

    expect($state->dotPath('title'))->toBe('title');

    $state->enterProperty('author', 'writer');

    expect($state->dotPath())->toBe('writer')
        ->and($state->dotPath('name'))->toBe('writer.name');

    $state->leave();
    $state->enterProperty('posts');
    $state->enterItem(0);

    expect($state->dotPath('title'))->toBe('posts.0.title')
        ->and($state->depth())->toBe(2);
});

it('records mappings on the current structure node', function () {
    $state = makeConstructionState();

    $state->recordMapping('author', 'writer');

    expect($state->structure())->toBe([
        'class' => SimpleData::class,
        'mappings' => ['author' => 'writer'],
        'children' => [],
    ]);
});

it('resolves original keys through mappings, defaulting to the property name', function () {
    $state = makeConstructionState();

    $state->recordMapping('author', 'writer');

    expect($state->originalKey('author'))->toBe('writer')
        ->and($state->originalKey('title'))->toBe('title');
});

it('creates one structure node per data property, ignoring collection indices', function () {
    $state = makeConstructionState();

    $state->enterProperty('posts');
    $state->enterItem(3);
    $state->recordMapping('title', 'post_title');
    $state->leave();
    $state->leave();

    expect($state->structure())->toBe([
        'class' => SimpleData::class,
        'mappings' => [],
        'children' => [
            'posts' => [
                'class' => null,
                'mappings' => ['title' => 'post_title'],
                'children' => [],
            ],
        ],
    ]);
});

it('sets and reads node classes for nested nodes', function () {
    $state = makeConstructionState();

    expect($state->nodeClass())->toBe(SimpleData::class);

    $state->enterProperty('author', 'writer');
    $state->setNodeClass(app(DataConfig::class)->getDataClass(SimpleData::class));

    expect($state->nodeClass())->toBe(SimpleData::class)
        ->and($state->structure()['children']['author']['class'])->toBe(SimpleData::class);
});

it('reading originalKey on an unvisited path creates no structure nodes', function () {
    $state = makeConstructionState();

    $state->enterProperty('author');

    expect($state->originalKey('name'))->toBe('name');

    $state->leave();

    expect($state->structure()['children'])->toBe([]);
});

it('reading nodeClass on an unvisited path creates no structure nodes', function () {
    $state = makeConstructionState();

    $state->enterProperty('author');

    expect($state->nodeClass())->toBeNull();

    $state->leave();

    expect($state->structure()['children'])->toBe([]);
});

it('records divergent item classes on the shared node', function () {
    $state = makeConstructionState();

    $state->enterProperty('items');
    $state->setNodeClass(app(DataConfig::class)->getDataClass(SimpleData::class));

    $state->enterItem(0);
    $state->setNodeClass(app(DataConfig::class)->getDataClass(SimpleData::class));
    $state->leave();

    $state->enterItem(1);
    $state->setNodeClass(app(DataConfig::class)->getDataClass(NestedData::class));
    $state->leave();

    $state->leave();

    expect($state->structure()['children']['items'])->toBe([
        'class' => SimpleData::class,
        'mappings' => [],
        'children' => [],
        'indexClasses' => [1 => NestedData::class],
    ]);
});

it('nodeClass on an item falls back to the shared class', function () {
    $state = makeConstructionState();

    $state->enterProperty('items');
    $state->setNodeClass(app(DataConfig::class)->getDataClass(SimpleData::class));
    $state->enterItem(0);

    expect($state->nodeClass())->toBe(SimpleData::class);

    $state->setNodeClass(app(DataConfig::class)->getDataClass(NestedData::class));

    expect($state->nodeClass())->toBe(NestedData::class);

    $state->leave();

    expect($state->nodeClass())->toBe(SimpleData::class);
});

it('writes values under string item keys', function () {
    $state = makeConstructionState();

    $state->enterProperty('items');
    $state->enterItem('foo');
    $state->writeValue('title', 'Hello');
    $state->leave();
    $state->leave();

    expect($state->payload())->toBe(['items' => ['foo' => ['title' => 'Hello']]]);
});

it('builds dot paths ending in integer zero', function () {
    $state = makeConstructionState();

    $state->enterProperty('posts');

    expect($state->dotPath(0))->toBe('posts.0');
});
