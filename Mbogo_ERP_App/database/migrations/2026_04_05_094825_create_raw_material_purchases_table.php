<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawMaterialPurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('raw_material_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->foreign('comp_unit_id')->references('id')->on('company_units')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('raw_material_id');
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->onDelete('restrict')->onUpdate('cascade');
            $table->date('purchase_date');
            $table->decimal('qty', 18, 2)->default(0);
            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('total_price', 18, 2)->default(0);
            $table->string('invoice_no')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('Purchased');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('raw_material_purchases');
    }
}