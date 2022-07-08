<?php

namespace Spatie\LaravelData\Tests\Normalizers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Tests\Factories\DataBlueprintFactory;
use Spatie\LaravelData\Tests\Factories\DataPropertyBlueprintFactory;
use Spatie\LaravelData\Tests\Fakes\FakeModelData;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;
use Spatie\LaravelData\Tests\Fakes\Models\FakeNestedModel;
use Spatie\LaravelData\Tests\TestCase;

class ModelNormalizerTest extends TestCase
{
    /** @test */
    public function it_can_get_a_data_object_from_model()
    {
        $model = FakeModel::factory()->create();

        $data = FakeModelData::from($model);

        $this->assertEquals($model->string, $data->string);
        $this->assertEquals($model->nullable, $data->nullable);
        $this->assertEquals($model->date, $data->date);
    }

    /** @test */
    public function it_can_get_a_data_object_with_nesting_from_model_and_relations()
    {
        $model = FakeModel::factory()->create();

        $nestedModelA = FakeNestedModel::factory()->for($model)->create();
        $nestedModelB = FakeNestedModel::factory()->for($model)->create();

        $data = FakeModelData::from($model->load('fakeNestedModels'));

        $this->assertEquals($model->string, $data->string);
        $this->assertEquals($model->nullable, $data->nullable);
        $this->assertEquals($model->date, $data->date);

        $this->assertCount(2, $data->fake_nested_models);

        $this->assertEquals($nestedModelA->string, $data->fake_nested_models[0]->string);
        $this->assertEquals($nestedModelA->nullable, $data->fake_nested_models[0]->nullable);
        $this->assertEquals($nestedModelA->date, $data->fake_nested_models[0]->date);

        $this->assertEquals($nestedModelB->string, $data->fake_nested_models[1]->string);
        $this->assertEquals($nestedModelB->nullable, $data->fake_nested_models[1]->nullable);
        $this->assertEquals($nestedModelB->date, $data->fake_nested_models[1]->date);
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

        $data = $dataClass::from($model);

        $this->assertTrue($data->date->eq(Carbon::create(2020, 05, 16, 00, 00, 00)));
        $this->assertTrue($data->datetime->eq(Carbon::create(2020, 05, 16, 12, 00, 00)));
        $this->assertTrue($data->immutable_date->eq(Carbon::create(2020, 05, 16, 00, 00, 00)));
        $this->assertTrue($data->immutable_datetime->eq(Carbon::create(2020, 05, 16, 12, 00, 00)));
        $this->assertTrue($data->created_at->eq(Carbon::create(2020, 05, 16, 12, 00, 00)));
        $this->assertTrue($data->updated_at->eq(Carbon::create(2020, 05, 16, 12, 00, 00)));
    }
}
