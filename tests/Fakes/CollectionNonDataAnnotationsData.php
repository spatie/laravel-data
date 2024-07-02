<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;

/**
 * @property \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum[] $propertyM
 * @property array<\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum> $propertyN
 * @property array<DummyBackedEnum> $propertyO
 * @property array<DummyBackedEnum>|null $propertyQ
 */
class CollectionNonDataAnnotationsData
{
    /** @var \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum[] */
    public array $propertyA;

    /** @var \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum[]|null */
    public ?array $propertyB;

    /** @var null|\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum[] */
    public ?array $propertyC;

    /** @var ?\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum[] */
    public array $propertyD;

    /** @var array<string> */
    public array $propertyE;

    /** @var array<string> */
    public array $propertyF;

    /** @var DummyBackedEnum[] */
    public array $propertyG;

    /** @var DummyBackedEnum */
    public array $propertyH; // FAIL

    public array $propertyI;

    /** @var array<\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum> */
    public array $propertyJ;

    /** @var LengthAwarePaginator<\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum> */
    public LengthAwarePaginator $propertyK;

    /** @var \Illuminate\Support\Collection<\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum> */
    public Collection $propertyL;

    public array $propertyM;

    public array $propertyN;

    public array $propertyO;

    /** @var \Illuminate\Support\Collection<Error> */
    public Collection $propertyP;

    public array $propertyQ;

    /** @var \Illuminate\Support\Collection<Error>|null */
    public ?Collection $propertyR;

    /**
     * @param \Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum[]|null $paramA
     * @param null|\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum[] $paramB
     * @param  ?\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum[] $paramC
     * @param ?\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum[] $paramD
     * @param \Spatie\LaravelData\DataCollection<\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum> $paramE
     * @param ?\Spatie\LaravelData\DataCollection<\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum> $paramF
     * @param DummyBackedEnum[] $paramG
     * @param array<DummyBackedEnum> $paramH
     * @param array<int,DummyBackedEnum> $paramJ
     * @param array<int, DummyBackedEnum> $paramI
     * @param \Spatie\LaravelData\DataCollection<\Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum>|null $paramK
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
        ?array $paramK,
    ) {

    }
}
