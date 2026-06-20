<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroLoanApplicantsTable extends Migration
{
    public function up()
    {
        Schema::create('micro_loan_applicants', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->foreign('comp_unit_id')->references('id')->on('company_units')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->string('applicant_type')->default('Individual');
            $table->string('full_name');
            $table->string('trading_as')->nullable();
            $table->string('national_id_no')->nullable();
            $table->string('passport_no')->nullable();
            $table->string('marital_status')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('age')->nullable();

            $table->string('postal_address')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('office_phone')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('work_email')->nullable();

            $table->string('residence_town')->nullable();
            $table->string('residence_estate')->nullable();
            $table->string('residence_street')->nullable();
            $table->string('house_no')->nullable();
            $table->string('residence_type')->nullable();
            $table->string('building_name')->nullable();
            $table->string('landmark')->nullable();

            $table->string('referred_by')->nullable();
            $table->string('referred_phone')->nullable();

            $table->string('employer')->nullable();
            $table->string('employment_terms')->nullable();
            $table->integer('contract_duration_months')->nullable();
            $table->date('employment_date')->nullable();
            $table->string('designation')->nullable();
            $table->string('payroll_no')->nullable();
            $table->decimal('gross_salary', 18, 2)->default(0);
            $table->decimal('net_salary', 18, 2)->default(0);
            $table->string('salary_pay_date')->nullable();
            $table->string('department')->nullable();
            $table->string('workstation')->nullable();
            $table->string('branch_name')->nullable();

            $table->string('business_name')->nullable();
            $table->string('business_type')->nullable();
            $table->string('kra_pin')->nullable();
            $table->string('business_tin')->nullable();
            $table->string('business_physical_address')->nullable();
            $table->string('business_town')->nullable();
            $table->string('business_building')->nullable();
            $table->string('nature_of_business')->nullable();
            $table->string('business_premise')->nullable();
            $table->string('business_landmark')->nullable();
            $table->decimal('annual_turnover', 18, 2)->default(0);
            $table->integer('years_in_business')->nullable();

            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_loan_applicants');
    }
}