<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroLoanRemindersTable extends Migration
{
    public function up()
    {
        Schema::create('micro_loan_reminders', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('loan_application_id')
                  ->nullable()
                  ->constrained('micro_loan_applications')
                  ->nullOnDelete();

            $table->foreignId('repayment_id')
                  ->nullable()
                  ->constrained('micro_loan_repayments')
                  ->nullOnDelete();

            // Reminder Info
            $table->string('reminder_type'); // SMS, Email, Call
            $table->text('message')->nullable();
            $table->date('reminder_date');

            // Status
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');

            // Created By
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_loan_reminders');
    }
}