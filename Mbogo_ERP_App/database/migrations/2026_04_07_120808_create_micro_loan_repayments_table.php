<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroLoanRepaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('micro_loan_repayments', function (Blueprint $table) {
            $table->id(); // ✅ muhimu sana

            // Loan Application
            $table->foreignId('loan_application_id')
                  ->constrained('micro_loan_applications')
                  ->cascadeOnDelete();

            // Repayment Details
            $table->date('repayment_date');

            $table->decimal('amount_paid', 18, 2)->default(0);
            $table->decimal('principal_paid', 18, 2)->default(0);
            $table->decimal('interest_paid', 18, 2)->default(0);
            $table->decimal('penalty_paid', 18, 2)->default(0);
            $table->decimal('reminder_charge_paid', 18, 2)->default(0);
            $table->decimal('recoverable_cost_paid', 18, 2)->default(0);

            // Payment Info
            $table->string('payment_method')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('remarks')->nullable();

            // Received By
            $table->foreignId('received_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Status
            $table->string('status')->default('Active');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_loan_repayments');
    }
}