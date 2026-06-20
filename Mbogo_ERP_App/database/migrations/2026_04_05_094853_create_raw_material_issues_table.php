<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawMaterialIssuesTable extends Migration
{
    public function up()
    {
        Schema::create('raw_material_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->foreign('comp_unit_id')->references('id')->on('company_units')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('raw_material_id');
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('manufacturing_request_id')->nullable();
            $table->unsignedBigInteger('issue_to_work_point_id')->nullable();
            $table->foreign('manufacturing_request_id')->references('id')->on('raw_material_requests')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('issue_to_work_point_id')->references('id')->on('work_points')->onDelete('restrict')->onUpdate('cascade');
            $table->string('issue_to_type')->default('Production');
            $table->string('issue_to_name')->nullable();
            $table->date('issue_date');
            $table->decimal('issued_qty', 18, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->string('status')->default('Issued');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('raw_material_issues');
    }
}

