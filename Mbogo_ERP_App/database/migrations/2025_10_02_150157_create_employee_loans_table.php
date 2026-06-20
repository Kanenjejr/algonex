<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // staff user id receiving advance/loan
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            $table->enum('type', ['Advance','Loan']); // Advance or Loan
            $table->decimal('amount', 15, 2);
            $table->decimal('balance', 15, 2)->nullable(); // remaining
            $table->integer('installments')->nullable(); // number of months
            $table->decimal('monthly_deduction', 15, 2)->nullable();
            $table->date('disbursed_at')->nullable();
            $table->string('status')->default('Active'); 
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('employee_loans');
    }
}