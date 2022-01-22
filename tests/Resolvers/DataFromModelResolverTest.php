<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Resolvers\DataFromModelResolver;
use Spatie\LaravelData\Tests\Factories\DataBlueprintFactory;
use Spatie\LaravelData\Tests\Factories\DataPropertyBlueprintFactory;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;

class DataFromModelResolverTest extends TestCase
{
    private DataFromModelResolver $resolver;

    public function setUp(): void
    {
        parent::setUp();

        $this->resolver = app(DataFromModelResolver::class);
    }

    /** @test */
    public function it_can_get_a_data_object_from_model()
    {
        $fakeModelClass = new class () extends Model {
        };

        $model = $fakeModelClass::make([
            'string' => 'Hello',
        ]);

        $data = $this->resolver->execute(
            SimpleData::class,
            $model
        );

        $this->assertEquals(new SimpleData('Hello'), $data);
    }

    /** @test */
    public function it_can_get_a_data_object_from_model_with_dates()
    {
        $fakeModelClass = new class () extends Model {
            protected $casts = [
                'date' => 'date',
                'datetime' => 'datetime',
                'immutable_date' => 'immutable_date',
                'immutable_datetime' => 'immutable_datetime',
            ];
        };

        $model = $fakeModelClass::make([
            'date' => Carbon::create(2020, 05, 16, 12, 00, 00),
            'datetime' => Carbon::create(2020, 05, 16, 12, 00, 00),
            'immutable_date' => Carbon::create(2020, 05, 16, 12, 00, 00),
            'immutable_datetime' => Carbon::create(2020, 05, 16, 12, 00, 00),
            'created_at' => Carbon::create(2020, 05, 16, 12, 00, 00),
            'updated_at' => Carbon::create(2020, 05, 16, 12, 00, 00),
        ]);

        $dataClass = DataBlueprintFactory::new('DataFromModelWithDates')
            ->withProperty(
                DataPropertyBlueprintFactory::new('date')->withType(Carbon::class),
                DataPropertyBlueprintFactory::new('datetime')->withType(Carbon::class),
                DataPropertyBlueprintFactory::new('immutable_date')->withType(CarbonImmutable::class),
                DataPropertyBlueprintFactory::new('immutable_datetime')->withType(CarbonImmutable::class),
                DataPropertyBlueprintFactory::new('created_at')->withType(Carbon::class),
                DataPropertyBlueprintFactory::new('updated_at')->withType(Carbon::class),
            )
            ->create();

        $data = $this->resolver->execute($dataClass, $model);

        $this->assertTrue($data->date->eq(Carbon::create(2020, 05, 16, 00, 00, 00)));
        $this->assertTrue($data->datetime->eq(Carbon::create(2020, 05, 16, 12, 00, 00)));
        $this->assertTrue($data->immutable_date->eq(Carbon::create(2020, 05, 16, 00, 00, 00)));
        $this->assertTrue($data->immutable_datetime->eq(Carbon::create(2020, 05, 16, 12, 00, 00)));
        $this->assertTrue($data->created_at->eq(Carbon::create(2020, 05, 16, 12, 00, 00)));
        $this->assertTrue($data->updated_at->eq(Carbon::create(2020, 05, 16, 12, 00, 00)));
    }
}
