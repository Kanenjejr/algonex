<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManufacturingMaterialStocksTable extends Migration
{
    public function up()
    {
        Schema::create('manufacturing_material_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->unsignedBigInteger('raw_material_id');
            $table->decimal('qty_in', 18, 2)->default(0);
            $table->decimal('qty_out', 18, 2)->default(0);
            $table->decimal('balance', 18, 2)->default(0);
            $table->string('status', 50)->default('Active');
            $table->timestamps();

            $table->unique(
                ['company_id', 'comp_unit_id', 'work_point_id', 'raw_material_id'],
                'uq_mfg_stock_scope'
            );

            $table->foreign('company_id')
                ->references('id')->on('company_sites')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->foreign('comp_unit_id')
                ->references('id')->on('company_units')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->foreign('work_point_id')
                ->references('id')->on('work_points')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->foreign('raw_material_id')
                ->references('id')->on('raw_materials')
                ->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('manufacturing_material_stocks');
    }
}