<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->string('period'); // e.g. "2025-09" or "September 2025"
            $table->date('prepared_at')->nullable();
            $table->unsignedBigInteger('prepared_by')->nullable();
            $table->date('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->date('paid_at')->nullable(); // cash-out date
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->string('status')->default('Draft'); // Draft, Prepared, Approved, Paid, Cancelled
            $table->decimal('gross_total', 18, 2)->default(0);
            $table->decimal('net_total', 18, 2)->default(0);
            // statutory default rates (stored so each payroll knows what rates were used)
            $table->decimal('nssf_employee_rate',5,2)->nullable(); // percent
            $table->decimal('nssf_employer_rate',5,2)->nullable();
            $table->decimal('psssf_rate',5,2)->nullable();
            $table->decimal('sdl_rate',5,2)->nullable();
            $table->decimal('wcf_rate',5,2)->nullable();
            
            $table->string('scope_type')->default('All'); // All, Exclude-NCL, Only-NCL
            $table->boolean('include_ncl')->default(true);
            $table->integer('days_in_month')->nullable();

            $table->decimal('allowance_total', 18, 2)->default(0);
            $table->decimal('bonus_total', 18, 2)->default(0);
            $table->decimal('absence_total', 18, 2)->default(0);
            $table->decimal('heslb_total', 18, 2)->default(0);
            $table->decimal('loan_total', 18, 2)->default(0);
            $table->decimal('paye_total', 18, 2)->default(0);
            $table->decimal('employer_cost_total', 18, 2)->default(0);
            $table->decimal('payroll_cost_total', 18, 2)->default(0);

            $table->timestamp('rolled_back_at')->nullable();
            $table->unsignedBigInteger('rolled_back_by')->nullable();
            $table->text('rollback_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('prepared_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
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
        Schema::dropIfExists('payrolls');
    }
}