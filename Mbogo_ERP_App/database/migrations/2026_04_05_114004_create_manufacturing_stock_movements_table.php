<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManufacturingStockMovementsTable extends Migration
{
    public function up()
    {
        Schema::create('manufacturing_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->date('movement_date');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->unsignedBigInteger('raw_material_id');
            $table->enum('reference_type', ['Receipt', 'Consumption', 'Adjustment']);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('qty_in', 18, 2)->default(0);
            $table->decimal('qty_out', 18, 2)->default(0);
            $table->decimal('balance_after', 18, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

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

            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('manufacturing_stock_movements');
    }
}