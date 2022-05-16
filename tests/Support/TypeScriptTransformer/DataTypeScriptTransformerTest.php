<?php

namespace Spatie\LaravelData\Tests\Support\TypeScriptTransformer;

use ReflectionClass;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptTransformer;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\TypeScriptTransformer\Attributes\Optional as TypeScriptOptional;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

class DataTypeScriptTransformerTest extends TestCase
{
    /** @test */
    public function it_can_covert_a_data_object_to_typescript()
    {
        $config = TypeScriptTransformerConfig::create();

        $data = new class (null, Optional::create(), 42, true, 'Hello world', 3.14, ['the', 'meaning', 'of', 'life'], Lazy::create(fn () => 'Lazy'), SimpleData::from('Simple data'), SimpleData::collection([]), SimpleData::collection([]), SimpleData::collection([])) extends Data {
            public function __construct(
                public null|int $nullable,
                public Optional | int $undefineable,
                public int $int,
                public bool $bool,
                public string $string,
                public float $float,
                /** @var string[] */
                public array $array,
                public Lazy|string $lazy,
                public SimpleData $simpleData,
                /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
                public DataCollection $dataCollection,
//              Types package is not smart enough
//                /** @var DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection $dataCollectionAlternative,
                #[DataCollectionOf(SimpleData::class)]
                public DataCollection $dataCollectionWithAttribute,
            ) {
            }
        };

        $transformer = new DataTypeScriptTransformer($config);

        $reflection = new ReflectionClass($data);

        $this->assertTrue($transformer->canTransform($reflection));
        $this->assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
    }

    /** @test */
    public function it_respects_optional_attribute()
    {
        $config = TypeScriptTransformerConfig::create();
        $config->nullToOptional(false);

        $data = new class (10, 'Ruben') extends Data {
            public function __construct(
                #[TypeScriptOptional]
                public ?int $id,
                public string $first_name,
            ) {
            }
        };

        $transformer = new DataTypeScriptTransformer($config);
        $reflection = new ReflectionClass($data);

        $this->assertTrue($transformer->canTransform($reflection));
        $this->assertEquals(<<<TXT
        {
        id?: number;
        first_name: string;
        }
        TXT, $transformer->transform($reflection, 'DataObject')->transformed);
    }

    /** @test */
    public function it_converts_nullable_properties_to_optional_ones()
    {
        $config = TypeScriptTransformerConfig::create();
        $config->nullToOptional(true);

        $data = new class (10, 'Ruben') extends Data {
            public function __construct(
                public ?int $id,
                public ?string $first_name,
            ) {
            }
        };

        $transformer = new DataTypeScriptTransformer($config);
        $reflection = new ReflectionClass($data);

        $this->assertTrue($transformer->canTransform($reflection));
        $this->assertEquals(<<<TXT
        {
        id?: number;
        first_name?: string;
        }
        TXT, $transformer->transform($reflection, 'DataObject')->transformed);
    }
}
