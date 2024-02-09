<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

/**
 * @property DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleData> $propertyO
 * @property \Spatie\LaravelData\Tests\Fakes\SimpleData[] $propertyP
 * @property DataCollection<SimpleData> $propertyQ
 * @property array<\Spatie\LaravelData\Tests\Fakes\SimpleData> $propertyR
 * @property \Spatie\LaravelData\Tests\Fakes\SimpleData[] $propertyS
 * @property array<SimpleData> $propertyT
 */
class CollectionAnnotationsData
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

    /** @var \Spatie\LaravelData\Lazy[] */
    public array $propertyJ; // Fail

    public DataCollection $propertyK;

    /** @var array<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
    public array $propertyL;

    /** @var LengthAwarePaginator<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
    public LengthAwarePaginator $propertyM;

    /** @var \Illuminate\Support\Collection<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
    public Collection $propertyN;

    public DataCollection $propertyO;

    public DataCollection $propertyP;

    public DataCollection $propertyQ;

    public array $propertyR;

    public array $propertyS;

    public array $propertyT;

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
