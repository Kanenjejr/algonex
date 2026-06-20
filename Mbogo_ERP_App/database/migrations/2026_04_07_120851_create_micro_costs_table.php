<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroCostsTable extends Migration
{
    public function up()
    {
        Schema::create('micro_costs', function (Blueprint $table) {
            $table->id();

            // Company Relations
            $table->foreignId('company_id')
                  ->nullable()
                  ->constrained('company_sites')
                  ->cascadeOnDelete();

            $table->foreignId('comp_unit_id')
                  ->nullable()
                  ->constrained('company_units')
                  ->cascadeOnDelete();

            $table->foreignId('work_point_id')
                  ->nullable()
                  ->constrained('work_points')
                  ->cascadeOnDelete();

            // Loan Application
            $table->foreignId('loan_application_id')
                  ->nullable()
                  ->constrained('micro_loan_applications')
                  ->nullOnDelete();

            // Cost Info
            $table->enum('cost_type', ['Office', 'ApplicantRecoverable'])->default('Office');
            $table->date('cost_date');
            $table->string('cost_name');
            $table->decimal('amount', 18, 2)->default(0);
            $table->text('remarks')->nullable();

            // Recorded By
            $table->foreignId('recorded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('status')->default('Active');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_costs');
    }
}