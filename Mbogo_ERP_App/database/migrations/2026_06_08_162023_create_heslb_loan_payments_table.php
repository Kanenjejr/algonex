<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeslbLoanPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('heslb_loan_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('heslb_loan_id');
            $table->unsignedBigInteger('payroll_id')->nullable();
            $table->unsignedBigInteger('payroll_line_id')->nullable();
            $table->unsignedBigInteger('user_id');

            $table->string('period')->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->decimal('balance_before', 18, 2)->default(0);
            $table->decimal('balance_after', 18, 2)->default(0);
            $table->string('status')->default('Posted'); // Posted, Reversed
            $table->text('notes')->nullable();

            $table->foreign('heslb_loan_id')->references('id')->on('heslb_loans')->onDelete('cascade');
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('set null');
            $table->foreign('payroll_line_id')->references('id')->on('payroll_lines')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('heslb_loan_payments');
    }
}
