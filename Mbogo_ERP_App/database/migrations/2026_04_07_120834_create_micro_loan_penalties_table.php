<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroLoanPenaltiesTable extends Migration
{
    public function up()
    {
        Schema::create('micro_loan_penalties', function (Blueprint $table) {
            $table->id();

            // Link to loan application
            $table->foreignId('loan_application_id')
                  ->constrained('micro_loan_applications')
                  ->cascadeOnDelete();

            // Penalty details
            $table->decimal('penalty_amount', 18, 2)->default(0);
            $table->date('penalty_date');
            $table->text('remarks')->nullable();

            $table->string('status')->default('Active');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_loan_penalties');
    }
}