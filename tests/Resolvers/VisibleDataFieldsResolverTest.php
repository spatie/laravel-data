<?php

use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Resolvers\VisibleDataFieldsResolver;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

function findVisibleFields(
    Data $data,
    TransformationContextFactory $contextFactory,
): array {
    return app(VisibleDataFieldsResolver::class)->execute(
        $data,
        app(DataConfig::class)->getDataClass($data::class),
        $contextFactory->get($data)
    );
}

it('will hide hidden fields', function () {
    $dataClass = new class () extends Data {
        public string $visible = 'visible';

        #[Hidden]
        public string $hidden = 'hidden';
    };

    expect(findVisibleFields($dataClass, TransformationContextFactory::create()))->toEqual([
        'visible' => null,
    ]);
});

it('will hide fields which are uninitialized', function () {
    $dataClass = new class () extends Data {
        public string $visible = 'visible';

        public Optional|string $optional;
    };

    expect(findVisibleFields($dataClass, TransformationContextFactory::create()))->toEqual([
        'visible' => null,
    ]);
});

it('can execute excepts', function (
    TransformationContextFactory $factory,
    array $expectedVisibleFields,
    array $expectedTransformed
) {
    $dataClass = new class() extends Data {
        /**
         * @param array<SimpleData> $collection
         */
        public function __construct(
            public string $string = 'string',
            public SimpleData $simple = new SimpleData('simple'),
            public NestedData $nested = new NestedData(new SimpleData('simple')),
            public array $collection = [
                new SimpleData('simple'),
                new SimpleData('simple'),
            ],
        ) {
        }
    };

    expect(findVisibleFields($dataClass, $factory))->toEqual($expectedVisibleFields);

    expect($dataClass->transform($factory))->toEqual($expectedTransformed);
})->with(function () {
    yield 'single field' => [
        'factory' => TransformationContextFactory::create()
            ->except('simple'),
        'fields' => [
            'string' => null,
            'nested' => new TransformationContext(),
            'collection' => new TransformationContext(),
        ],
        'transformed' => [
            'string' => 'string',
            'nested' => [
                'simple' => ['string' => 'simple'],
            ],
            'collection' => [
                [
                    'simple' => 'simple',
                ],
                [
                    'simple' => 'simple',
                ],
            ],
        ],
    ];
});

// TODO write tests

it('can perform an excepts', function () {
    //    $dataClass = new class() extends Data {
    //        public function __construct(
    //            public string $visible = 'visible',
    //            public SimpleData $simple = new SimpleData('simple'),
    //            public NestedData $nestedData = new NestedData(new SimpleData('simple')),
    //            public array $collection =
    //        ) {
    //        }
    //    };
    //
    //    expect(findVisibleFields($dataClass, TransformationContextFactory::create()))->toMatchArray([
    //        'multi' => new TransformationContext(
    //            transformValues: true,
    //            mapPropertyNames: true,
    //            wrapExecutionType: true,
    //            new SplObjectStorage(),
    //            new SplObjectStorage(),
    //            new SplObjectStorage(),
    //            new SplObjectStorage(),
    //        ),
    //    ]);
});
