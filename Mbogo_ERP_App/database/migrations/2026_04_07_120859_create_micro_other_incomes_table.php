<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroOtherIncomesTable extends Migration
{
    public function up()
    {
        Schema::create('micro_other_income', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->foreign('comp_unit_id')->references('id')->on('company_units')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('loan_application_id')->nullable();
            $table->foreign('loan_application_id')->references('id')->on('micro_loan_applications')->onDelete('set null')->onUpdate('cascade');

            $table->date('income_date');
            $table->string('income_name');
            $table->decimal('amount', 18, 2)->default(0);
            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');

            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_other_income');
    }
}