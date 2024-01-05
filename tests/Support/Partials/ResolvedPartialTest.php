<?php

use Spatie\LaravelData\Support\Partials\ResolvedPartial;
use Spatie\LaravelData\Support\Partials\Segments\AllPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\FieldsPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\NestedPartialSegment;

it('can use the pointer system when ending in a field', function () {
    $partial = new ResolvedPartial([
        new NestedPartialSegment('struct'),
        new FieldsPartialSegment(['name', 'age']),
    ]);

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe('struct');
    expect($partial->getFields())->toBe(null);

    $partial->next(); // level 1

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(['name', 'age']);

    $partial->rollbackWhenRequired(); // level 0

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe('struct');
    expect($partial->getFields())->toBe(null);

    $partial->next(); // level 1
    $partial->next(); // level 2 - non existing

    expect($partial->isUndefined())->toBeTrue();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 1

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(['name', 'age']);

    $partial->next(); // level 2 - non existing
    $partial->next(); // level 3 - non existing

    expect($partial->isUndefined())->toBeTrue();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 2 - non existing

    expect($partial->isUndefined())->toBeTrue();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 1

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(['name', 'age']);

    $partial->rollbackWhenRequired(); // level 0

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe('struct');
    expect($partial->getFields())->toBe(null);
});

it('can use the pointer system when ending in an all', function (){
    $partial = new ResolvedPartial([
        new NestedPartialSegment('struct'),
        new AllPartialSegment(),
    ]);

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe('struct');
    expect($partial->getFields())->toBe(null);

    $partial->next(); // level 1

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeTrue();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 0

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe('struct');
    expect($partial->getFields())->toBe(null);

    $partial->next(); // level 1
    $partial->next(); // level 2 - non existing

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeTrue();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 1

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeTrue();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->next(); // level 2 - non existing
    $partial->next(); // level 3 - non existing

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeTrue();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 2 - non existing

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeTrue();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 1

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeTrue();
    expect($partial->getNested())->toBe(null);
    expect($partial->getFields())->toBe(null);

    $partial->rollbackWhenRequired(); // level 0

    expect($partial->isUndefined())->toBeFalse();
    expect($partial->isAll())->toBeFalse();
    expect($partial->getNested())->toBe('struct');
    expect($partial->getFields())->toBe(null);
});
