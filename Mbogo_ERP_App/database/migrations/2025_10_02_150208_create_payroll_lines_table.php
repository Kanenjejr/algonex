<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollLinesTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            // Earnings
            $table->decimal('basic_salary', 18, 2)->default(0);
            $table->decimal('allowances', 18, 2)->default(0);
            $table->decimal('overtime_payment', 18, 2)->default(0);
            $table->decimal('gross', 18, 2)->default(0);
            // Employee deductions
            $table->decimal('paye', 18, 2)->default(0);
            $table->decimal('nssf_employee', 18, 2)->default(0);
            $table->decimal('psssf', 18, 2)->default(0);
            $table->decimal('loan_deduction', 18, 2)->default(0);
            $table->decimal('absence_deduction', 18, 2)->default(0);
            $table->decimal('total_deductions', 18, 2)->default(0);
            // Employer statutory costs
            $table->decimal('nssf_employer', 18, 2)->default(0);
            $table->decimal('sdl', 18, 2)->default(0);
            $table->decimal('wcf', 18, 2)->default(0);
            $table->decimal('employer_cost', 18, 2)->default(0);
            // Final employee and company totals
            $table->decimal('net_pay', 18, 2)->default(0);
            $table->decimal('total_payroll_cost', 18, 2)->default(0);
            $table->decimal('bonus', 18, 2)->default(0);
            $table->integer('calendar_days')->nullable();
            $table->decimal('absent_days', 8, 2)->default(0);
            $table->decimal('paid_days', 8, 2)->default(0);
            $table->decimal('daily_rate', 18, 2)->default(0);

            $table->decimal('heslb_deduction', 18, 2)->default(0);
            $table->decimal('heslb_balance_before', 18, 2)->default(0);
            $table->decimal('heslb_balance_after', 18, 2)->default(0);

            $table->decimal('previous_net_pay', 18, 2)->default(0);
            $table->decimal('net_variation', 18, 2)->default(0);
            $table->decimal('gross_variation', 18, 2)->default(0);
            $table->text('note')->nullable();
            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('payroll_lines');
    }
}