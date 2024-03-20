<?php

use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;
use function Pest\Laravel\withExceptionHandling;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Iterables\LengthAwareDataPaginator;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('can paginate data', function () {
    withExceptionHandling();

    Route::get('/', function () {
        return (new LengthAwareDataPaginator(['Hello', 'World', 'Welcome'], 3, 1))->data(SimpleData::class);
        //
        //
        //        return SimpleData::collect(["Hello", "World", "Weclome"], LengthAwareDataPaginator::class)->data(
        //            include: 'single',
        //            exclude: ['multiple', 'items'],
        //            only:  [
        //                'callable' => fn(Data $data) => $data->property === 'something',
        //            ],
        //            includePermanently: 'permanently',
        //            wrap: null, // default
        //            // wrap: false // disabled
        //            // wrap: 'key' // defined
        //            transformValues: true, //
        //            mapPropertyNames: true,
        //        );
    });

    get('/')->dump();
});
