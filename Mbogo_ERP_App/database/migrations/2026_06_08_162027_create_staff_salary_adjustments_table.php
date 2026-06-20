<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffSalaryAdjustmentsTable extends Migration
{
    public function up()
    {
        Schema::create('staff_salary_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            $table->string('period'); // YYYY-MM
            $table->enum('type', ['Allowance', 'Bonus']);
            $table->enum('calc_type', ['Fixed', 'Percent'])->default('Fixed');
            $table->decimal('rate', 8, 2)->nullable();     // e.g. 2 or 5
            $table->decimal('amount', 18, 2)->default(0);  // fixed amount or calculated amount snapshot
            $table->string('status')->default('Active');   // Active, Inactive, Deleted
            $table->text('note')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();

            $table->index(['user_id', 'period', 'type', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_salary_adjustments');
    }
}
