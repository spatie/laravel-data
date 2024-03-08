<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

/**
 * @property DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleData> $propertyN
 * @property \Spatie\LaravelData\Tests\Fakes\SimpleData[] $propertyO
 * @property DataCollection<SimpleData> $propertyP
 * @property array<\Spatie\LaravelData\Tests\Fakes\SimpleData> $propertyQ
 * @property \Spatie\LaravelData\Tests\Fakes\SimpleData[] $propertyR
 * @property array<SimpleData> $propertyS
 */
class CollectionDataAnnotationsData
{
    /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
    public array $propertyA;

    /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[]|null */
    public ?array $propertyB;

    /** @var null|\Spatie\LaravelData\Tests\Fakes\SimpleData[] */
    public ?array $propertyC;

    /** @var ?\Spatie\LaravelData\Tests\Fakes\SimpleData[] */
    public array $propertyD;

    /** @var \Spatie\LaravelData\DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
    public DataCollection $propertyE;

    /** @var ?\Spatie\LaravelData\DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
    public ?DataCollection $propertyF;

    /** @var SimpleData[] */
    public array $propertyG;

    #[DataCollectionOf(SimpleData::class)]
    public DataCollection $propertyH;

    /** @var SimpleData */
    public DataCollection $propertyI; // FAIL

    public DataCollection $propertyJ;

    /** @var array<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
    public array $propertyK;

    /** @var LengthAwarePaginator<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
    public LengthAwarePaginator $propertyL;

    /** @var \Illuminate\Support\Collection<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
    public Collection $propertyM;

    public DataCollection $propertyN;

    public DataCollection $propertyO;

    public DataCollection $propertyP;

    public array $propertyQ;

    public array $propertyR;

    public array $propertyS;

    /**
     * @param \Spatie\LaravelData\Tests\Fakes\SimpleData[]|null $paramA
     * @param null|\Spatie\LaravelData\Tests\Fakes\SimpleData[] $paramB
     * @param  ?\Spatie\LaravelData\Tests\Fakes\SimpleData[] $paramC
     * @param ?\Spatie\LaravelData\Tests\Fakes\SimpleData[] $paramD
     * @param \Spatie\LaravelData\DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleData> $paramE
     * @param ?\Spatie\LaravelData\DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleData> $paramF
     * @param SimpleData[] $paramG
     * @param array<SimpleData> $paramH
     * @param array<int,SimpleData> $paramJ
     * @param array<int, SimpleData> $paramI
     */
    public function method(
        array $paramA,
        ?array $paramB,
        ?array $paramC,
        array $paramD,
        DataCollection $paramE,
        ?DataCollection $paramF,
        array $paramG,
        array $paramJ,
        array $paramI,
    ) {

    }
}
