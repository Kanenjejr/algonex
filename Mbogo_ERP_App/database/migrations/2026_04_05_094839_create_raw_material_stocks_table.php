<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawMaterialStocksTable extends Migration
{
    public function up()
    {
        Schema::create('raw_material_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->foreign('comp_unit_id')->references('id')->on('company_units')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('raw_material_id');
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->onDelete('restrict')->onUpdate('cascade');
            $table->decimal('qty_in', 18, 2)->default(0);
            $table->decimal('qty_out', 18, 2)->default(0);
            $table->decimal('balance', 18, 2)->default(0);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('raw_material_stocks');
    }
}