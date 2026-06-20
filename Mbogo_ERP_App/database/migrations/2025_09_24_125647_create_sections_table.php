<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->foreign('comp_unit_id')->references('id')->on('company_units')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('dept_id')->nullable();
            $table->foreign('dept_id')->references('id')->on('departments')->onDelete('cascade')->onUpdate('cascade');
            $table->string('secName');
            $table->string('secCode')->nullable();
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
        Schema::dropIfExists('sections');
    }
}