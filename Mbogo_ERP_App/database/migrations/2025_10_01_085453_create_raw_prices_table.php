<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('Raw_id')->nullable();
            $table->foreign('Raw_id')->references('id')->on('raw_materials')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('User_id')->nullable();
            $table->foreign('User_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->double('RawPrice');
            $table->string('Status')->default('Active');
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
        Schema::dropIfExists('raw_prices');
    }
}
