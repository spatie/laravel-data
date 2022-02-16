<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('fake_models', function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->string('string');
            $table->string('nullable')->nullable();
            $table->string('date');

            $table->timestamps();
        });

        Schema::create('fake_nested_models', function (Blueprint $table): void {
            $table->bigIncrements('id');

            $table->string('string');
            $table->string('nullable')->nullable();
            $table->string('date');

            $table->bigInteger('fake_model_id')->nullable();
            $table->foreign('fake_model_id')->references('id')->on('fake_models')->cascadeOnDelete();

            $table->timestamps();
        });
    }
};
