<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManufacturingReceiptsTable extends Migration
{
    public function up()
    {
        Schema::create('manufacturing_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique();
            $table->date('receipt_date');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->unsignedBigInteger('raw_material_request_id')->nullable();
            $table->unsignedBigInteger('raw_material_issue_id')->nullable();
            $table->unsignedBigInteger('raw_material_id');
            $table->decimal('received_qty', 18, 2)->default(0);
            $table->string('unit_name', 100)->nullable();
            $table->integer('no_of_bags')->nullable();
            $table->decimal('bag_size', 18, 2)->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status', 50)->default('Received');
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

            $table->foreign('raw_material_request_id')
                ->references('id')->on('raw_material_requests')
                ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('raw_material_issue_id')
                ->references('id')->on('raw_material_issues')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->foreign('raw_material_id')
                ->references('id')->on('raw_materials')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->foreign('received_by')
                ->references('id')->on('users')
                ->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('manufacturing_receipts');
    }
}