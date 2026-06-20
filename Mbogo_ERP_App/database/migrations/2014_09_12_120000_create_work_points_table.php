<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->foreign('comp_unit_id')->references('id')->on('company_units')->onDelete('cascade')->onUpdate('cascade');
            $table->string('work_code')->nullable();
            $table->string('work_name');
            $table->string('location');
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('user_id')->nullable();
            $table->string('phone_No')->nullable();
            $table->string('status')->default('Active');
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
        Schema::dropIfExists('work_points');
    }
}
