<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('services', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | COMPANY
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('company_id');

            /*
            |--------------------------------------------------------------------------
            | BUSINESS UNIT
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('business_unit_id');

            /*
            |--------------------------------------------------------------------------
            | WORK POINT
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('work_point_id');

            /*
            |--------------------------------------------------------------------------
            | SERVICE DETAILS
            |--------------------------------------------------------------------------
            */

            $table->string('service_code')->unique();

            $table->string('service_name');

            /*
            |--------------------------------------------------------------------------
            | PRICING
            |--------------------------------------------------------------------------
            */

            $table->decimal('price', 15, 4)
                  ->default(0);

            /*
            |--------------------------------------------------------------------------
            | UNIT
            |--------------------------------------------------------------------------
            */

            $table->string('unit')
                  ->nullable();

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'Active',
                'Inactive'
            ])->default('Active');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

    public function down()
    {
        Schema::dropIfExists('services');
    }
}